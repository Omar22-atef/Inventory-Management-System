<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesController extends Controller
{
    /**
     * Create a sale.
     * Expected payload:
     * {
     *   "customer_name": "Ali",
     *   "items": [
     *     {"product_id": 1, "quantity": 2},
     *     {"product_id": 3, "quantity": 1}
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
        ]);

        // Use DB transaction to keep everything atomic
        return DB::transaction(function() use ($data, $request) {
            $items = $data['items'];
            $productIds = collect($items)->pluck('product_id')->unique()->values()->all();

            // Load products in a single query
            $products = DB::table('products')->whereIn('id', $productIds)->get()->keyBy('id');

            // Validate stock availability first
            foreach ($items as $it) {
                $pid = $it['product_id'];
                $qty = (float) $it['quantity'];
                $product = $products->get($pid);

                if (! $product) {
                    return response()->json(['message' => "Product {$pid} not found"], 404);
                }

                $available = $product->quantity ?? 0;
                if ($available < $qty) {
                    return response()->json([
                        'message' => 'Insufficient stock',
                        'product_id' => $pid,
                        'available' => $available,
                        'requested' => $qty
                    ], 400);
                }
            }

            // Create sale row in sales table (if exists) or fallback to returning details without DB persistence
            $saleId = null;
            $saleCreated = false;
            if (Schema::hasTable('sales')) {
                $saleId = DB::table('sales')->insertGetId([
                    'customer_name' => $data['customer_name'] ?? null,
                    'total' => 0, // will update after computing lines
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $saleCreated = true;
            }

            $lineItems = [];
            $total = 0;

            // For each item: compute price, decrease products.quantity, insert sale_items if table exists, log stock
            foreach ($items as $it) {
                $pid = $it['product_id'];
                $qty = (float) $it['quantity'];
                $product = $products->get($pid);

                // Determine unit price: prefer existing price column; fallback to 0
                $unitPrice = property_exists($product, 'price') ? $product->price : 0;
                // If price stored as varchar in DB, cast to float safely
                $unitPrice = is_numeric($unitPrice) ? (float) $unitPrice : 0.0;

                $lineTotal = $unitPrice * $qty;
                $total += $lineTotal;

                // decrement products.quantity (lock row)
                // prefer product_stocks if table exists (but per your choice we rely on products.quantity)
                if (Schema::hasTable('product_stocks')) {
                    // try decrement in product_stocks (no models)
                    $psQuery = DB::table('product_stocks')->where('product_id', $pid);
                    $psRow = $psQuery->lockForUpdate()->first();
                    if ($psRow) {
                        if ($psRow->quantity < $qty) {
                            return response()->json(['message' => 'Insufficient stock in product_stocks', 'product_id'=>$pid], 400);
                        }
                        DB::table('product_stocks')->where('id', $psRow->id)
                            ->update(['quantity' => DB::raw("quantity - ({$qty})"), 'updated_at' => now()]);
                    } else {
                        // If no product_stocks row, fallback to products.quantity below
                        $p = DB::table('products')->where('id', $pid)->lockForUpdate()->first();
                        if (! $p || ($p->quantity ?? 0) < $qty) {
                            return response()->json(['message'=>'Insufficient stock', 'product_id'=>$pid], 400);
                        }
                        DB::table('products')->where('id', $pid)
                            ->update(['quantity' => DB::raw("GREATEST(0, quantity - ({$qty}))"), 'updated_at' => now()]);
                    }
                } else {
                    // operate on products.quantity
                    $p = DB::table('products')->where('id', $pid)->lockForUpdate()->first();
                    if (! $p || ($p->quantity ?? 0) < $qty) {
                        return response()->json(['message' => 'Insufficient stock', 'product_id' => $pid], 400);
                    }
                    DB::table('products')->where('id', $pid)
                        ->update(['quantity' => DB::raw("GREATEST(0, quantity - ({$qty}))"), 'updated_at' => now()]);
                }

                // Insert sale_items row if table exists
                if (Schema::hasTable('sale_items')) {
                    DB::table('sale_items')->insert([
                        'sale_id' => $saleId,
                        'product_id' => $pid,
                        'quantity' => $qty,
                        'price' => $unitPrice,
                        'line_total' => $lineTotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Insert into stock_logs for audit
                if (Schema::hasTable('stock_logs')) {
                    // If you have StockLog model it's fine, but we use DB to be model-free
                    DB::table('stock_logs')->insert([
                        'product_id' => $pid,
                        'type' => 'out',
                        'quantity' => $qty,
                        'note' => 'sale' . ($saleId ? " #{$saleId}" : ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $lineItems[] = [
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            // Update sale total if sale row was created
            if ($saleCreated) {
                DB::table('sales')->where('id', $saleId)->update(['total' => $total, 'updated_at' => now()]);
            }

            // Build response
            $response = [
                'sale_id' => $saleId,
                'customer_name' => $data['customer_name'] ?? null,
                'total' => $total,
                'items' => $lineItems,
                'created_at' => now(),
            ];

            return response()->json($response, 201);
        });
    }
}
