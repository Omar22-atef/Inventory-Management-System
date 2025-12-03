<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurchaseOrderController extends Controller
{
    /**
     * GET /api/purchase-orders
     * list purchase orders (paginated)
     */
    public function index(Request $request)
    {
        $q = DB::table('purchase_orders')->orderByDesc('created_at');

        if ($request->has('supplier_id')) $q->where('supplier_id', $request->supplier_id);
        if ($request->has('status')) $q->where('status', $request->status);

        return response()->json($q->paginate(20));
    }

    /**
     * GET /api/purchase-orders/{id}
     * show PO with items (if purchase_order_items table exists)
     */
    public function show($id)
    {
        $po = DB::table('purchase_orders')->where('id', $id)->first();
        if (! $po) return response()->json(['message' => 'Purchase order not found'], 404);

        $items = [];
        if (Schema::hasTable('purchase_order_items')) {
            $items = DB::table('purchase_order_items')->where('purchase_order_id', $id)->get();
        }

        return response()->json(['po' => $po, 'items' => $items]);
    }

    /**
     * POST /api/purchase-orders
     * Create a new purchase order with items
     * expected payload:
     * {
     *   "supplier_id": 1,
     *   "notes": "...",
     *   "items": [
     *     {"product_id":1,"quantity":10,"unit_price":100},
     *     ...
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function() use ($data, $request) {
            $total = 0;
            foreach ($data['items'] as $it) {
                $qty = (float) $it['quantity'];
                $price = isset($it['unit_price']) && is_numeric($it['unit_price']) ? (float)$it['unit_price'] : 0.0;
                $total += $qty * $price;
            }

            $poId = DB::table('purchase_orders')->insertGetId([
                'supplier_id' => $data['supplier_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'total' => $total,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('purchase_order_items')) {
                foreach ($data['items'] as $it) {
                    DB::table('purchase_order_items')->insert([
                        'purchase_order_id' => $poId,
                        'product_id' => $it['product_id'],
                        'quantity' => $it['quantity'],
                        'unit_price' => $it['unit_price'] ?? 0,
                        'received_quantity' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return response()->json(['message' => 'Purchase order created', 'purchase_order_id' => $poId], 201);
        });
    }

    /**
     * POST /api/purchase-orders/{id}/receive
     * Receive either full PO or specific items.
     * Payload options:
     * { "items": [{"purchase_item_id": 123, "quantity": 5}, ...] }
     * or empty items -> receive all remaining quantities for the PO
     *
     * This function will:
     * - increment product stock (products.quantity or product_stocks)
     * - update purchase_order_items.received_quantity (if exists)
     * - optionally mark purchase_orders.status => 'received' if everything received
     * - insert records in stock_logs table (if exists)
     */
    public function receive(Request $request, $id)
    {
        $data = $request->validate([
            'items' => 'nullable|array',
            'items.*.purchase_item_id' => 'required_with:items|integer',
            'items.*.quantity' => 'required_with:items|numeric|min:0.0001',
        ]);

        return DB::transaction(function() use ($data, $id, $request) {
            $po = DB::table('purchase_orders')->where('id', $id)->lockForUpdate()->first();
            if (! $po) return response()->json(['message' => 'Purchase order not found'], 404);

            // load items
            $poItems = [];
            if (Schema::hasTable('purchase_order_items')) {
                $poItems = DB::table('purchase_order_items')->where('purchase_order_id', $id)->lockForUpdate()->get()->keyBy('id');
                if ($data['items'] ?? false) {
                    // validate requested purchase_item_ids exist in this PO
                    foreach ($data['items'] as $it) {
                        if (! isset($poItems[$it['purchase_item_id']])) {
                            return response()->json(['message' => 'purchase_item_id not part of this PO: '.$it['purchase_item_id']], 400);
                        }
                    }
                }
            } else {
                // No purchase_order_items table: can't receive itemized; fallback to mark PO received
                // but nothing to increment except optionally total -> we will just mark PO received
                DB::table('purchase_orders')->where('id', $id)->update(['status' => 'received', 'updated_at' => now()]);
                return response()->json(['message' => 'Purchase order marked received (no items table present)'], 200);
            }

            $toReceive = [];
            if (! empty($data['items'])) {
                // use supplied item list
                foreach ($data['items'] as $it) {
                    $row = $poItems[$it['purchase_item_id']];
                    $remaining = ($row->quantity - ($row->received_quantity ?? 0));
                    $qtyToReceive = min((float)$it['quantity'], (float)$remaining);
                    if ($qtyToReceive <= 0) continue;
                    $toReceive[] = ['row' => $row, 'qty' => $qtyToReceive];
                }
            } else {
                // receive all remaining quantities in PO
                foreach ($poItems as $row) {
                    $remaining = ($row->quantity - ($row->received_quantity ?? 0));
                    if ($remaining > 0) {
                        $toReceive[] = ['row' => $row, 'qty' => (float)$remaining];
                    }
                }
            }

            if (empty($toReceive)) {
                return response()->json(['message' => 'Nothing to receive'], 400);
            }

            // Process each received line: increment product stock and update received_quantity
            foreach ($toReceive as $entry) {
                $row = $entry['row'];
                $qty = (float) $entry['qty'];
                $productId = $row->product_id;

                // Prefer product_stocks if present (warehouse info not stored in PO item; we update products.quantity)
                if (Schema::hasTable('product_stocks')) {
                    // Attempt to find a product_stocks row (no warehouse info in PO) - increment any matching row or create a new one
                    $ps = DB::table('product_stocks')->where('product_id', $productId)->lockForUpdate()->first();
                    if ($ps) {
                        DB::table('product_stocks')->where('id', $ps->id)
                            ->update(['quantity' => DB::raw("quantity + ({$qty})"), 'updated_at' => now()]);
                    } else {
                        DB::table('product_stocks')->insert([
                            'product_id' => $productId,
                            'warehouse_id' => null,
                            'quantity' => $qty,
                            'reorder_threshold' => 0,
                            'reorder_qty' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } elseif (Schema::hasTable('products') && Schema::hasColumn('products','quantity')) {
                    // lock product row and increment
                    $p = DB::table('products')->where('id', $productId)->lockForUpdate()->first();
                    if (! $p) {
                        return response()->json(['message' => 'Product not found: '.$productId], 404);
                    }
                    DB::table('products')->where('id', $productId)
                        ->update(['quantity' => DB::raw("COALESCE(quantity,0) + ({$qty})"), 'updated_at' => now()]);
                } else {
                    return response()->json(['message' => 'No place to store received stock (no product_stocks or products.quantity)'], 500);
                }

                // update received_quantity in purchase_order_items
                DB::table('purchase_order_items')->where('id', $row->id)
                    ->update([
                        'received_quantity' => DB::raw("COALESCE(received_quantity,0) + ({$qty})"),
                        'updated_at' => now()
                    ]);

                // log in stock_logs if exists
                if (Schema::hasTable('stock_logs')) {
                    DB::table('stock_logs')->insert([
                        'product_id' => $productId,
                        'type' => 'in',
                        'quantity' => $qty,
                        'note' => 'PO#'.$id.' receive',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // After processing, determine if all items fully received -> mark PO received
            $all = DB::table('purchase_order_items')->where('purchase_order_id', $id)->get();
            $fully = true;
            foreach ($all as $r) {
                if (($r->quantity - ($r->received_quantity ?? 0)) > 0) {
                    $fully = false;
                    break;
                }
            }

            DB::table('purchase_orders')->where('id', $id)
                ->update(['status' => $fully ? 'received' : 'partial', 'updated_at' => now()]);

            return response()->json(['message' => 'Received processed', 'purchase_order_id' => $id], 200);
        });
    }

    /**
     * POST /api/purchase-orders/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        $po = DB::table('purchase_orders')->where('id', $id)->first();
        if (! $po) return response()->json(['message' => 'Purchase order not found'], 404);
        if ($po->status === 'received') {
            return response()->json(['message' => 'Cannot cancel a fully received PO'], 400);
        }

        DB::table('purchase_orders')->where('id', $id)->update(['status' => 'cancelled', 'updated_at' => now()]);
        return response()->json(['message' => 'Purchase order cancelled'], 200);
    }
}
