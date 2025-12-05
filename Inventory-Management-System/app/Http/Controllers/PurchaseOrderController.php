<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurchaseOrderController extends Controller
{
    // GET /api/v1/purchase-orders
    public function index(Request $request)
    {
        if (! Schema::hasTable('purchase_orders')) {
            return response()->json(['message'=>'purchase_orders table not found'],404);
        }
        $q = DB::table('purchase_orders')->orderByDesc('created_at');
        if ($request->has('supplier_id')) $q->where('supplier_id', $request->supplier_id);
        if ($request->has('status')) $q->where('status', $request->status);
        return response()->json($q->paginate(20));
    }

    // GET /api/v1/purchase-orders/{id}
    public function show($id)
    {
        $po = DB::table('purchase_orders')->where('id',$id)->first();
        if (! $po) return response()->json(['message'=>'PO not found'],404);

        $items = [];
        if (Schema::hasTable('purchase_order_items')) {
            $items = DB::table('purchase_order_items')->where('purchase_order_id',$id)->get();
        }

        return response()->json(['po'=>$po,'items'=>$items]);
    }

    // POST /api/v1/purchase-orders
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'=>'nullable|exists:suppliers,id',
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|integer|exists:products,id',
            'items.*.quantity'=>'required|numeric|min:0.0001',
            'items.*.unit_price'=>'nullable|numeric|min:0'
        ]);

        return DB::transaction(function() use ($data) {
            $total = 0;
            foreach ($data['items'] as $it) {
                $qty = (float)$it['quantity'];
                $price = isset($it['unit_price']) ? (float)$it['unit_price'] : 0.0;
                $total += $qty * $price;
            }

            $poId = DB::table('purchase_orders')->insertGetId([
                'supplier_id'=>$data['supplier_id'] ?? null,
                'status'=>'pending',
                'total_price'=>$total,
                'created_at'=>now(),
                'updated_at'=>now()
            ]);

            if (Schema::hasTable('purchase_order_items')) {
                foreach ($data['items'] as $it) {
                    $qty = (float)$it['quantity'];
                    $price = isset($it['unit_price']) ? (float)$it['unit_price'] : 0.0;
                    DB::table('purchase_order_items')->insert([
                        'purchase_order_id'=>$poId,
                        'product_id'=>$it['product_id'],
                        'quantity'=>$qty,
                        'unit_price'=>$price,
                        'total_price'=>($qty * $price),
                        'created_at'=>now(),
                        'updated_at'=>now()
                    ]);
                }
            }

            return response()->json(['purchase_order_id'=>$poId,'total_price'=>$total],201);
        });
    }

    // POST /api/v1/purchase-orders/{id}/receive
    // This receives all items of PO (since purchase_order_items does not have a received_quantity column)
    public function receive(Request $request, $id)
    {
        if (! Schema::hasTable('purchase_orders')) return response()->json(['message'=>'PO table not found'],404);
        if (! Schema::hasTable('purchase_order_items')) return response()->json(['message'=>'PO items table not found'],404);

        return DB::transaction(function() use ($id, $request) {
            $po = DB::table('purchase_orders')->where('id',$id)->lockForUpdate()->first();
            if (! $po) return response()->json(['message'=>'PO not found'],404);

            $items = DB::table('purchase_order_items')->where('purchase_order_id',$id)->get();

            if ($items->isEmpty()) return response()->json(['message'=>'No items to receive'],400);

            foreach ($items as $it) {
                $pid = $it->product_id;
                $qty = (float)$it->quantity;

                // increment product quantity
                if (Schema::hasTable('product_stocks')) {
                    $ps = DB::table('product_stocks')->where('product_id',$pid)->lockForUpdate()->first();
                    if ($ps) {
                        DB::table('product_stocks')->where('id',$ps->id)->update(['quantity'=>DB::raw("quantity + ({$qty})"), 'updated_at'=>now()]);
                    } else {
                        DB::table('product_stocks')->insert([
                            'product_id'=>$pid,
                            'warehouse_id'=>null,
                            'quantity'=>$qty,
                            'reorder_threshold'=>0,
                            'reorder_qty'=>0,
                            'created_at'=>now(),
                            'updated_at'=>now()
                        ]);
                    }
                } else {
                    $prod = DB::table('products')->where('id',$pid)->lockForUpdate()->first();
                    if (! $prod) return response()->json(['message'=>'Product not found: '.$pid],404);
                    DB::table('products')->where('id',$pid)->update(['quantity'=>DB::raw("COALESCE(quantity,0) + ({$qty})"), 'updated_at'=>now()]);
                }

                // log
                if (Schema::hasTable('stock_logs')) {
                    DB::table('stock_logs')->insert([
                        'product_id'=>$pid,
                        'type'=>'in',
                        'quantity'=>$qty,
                        'note'=>'PO#'.$id.' receive',
                        'created_at'=>now(),
                        'updated_at'=>now()
                    ]);
                }
            }

            // mark PO as received
            DB::table('purchase_orders')->where('id',$id)->update(['status'=>'received','updated_at'=>now()]);

            return response()->json(['message'=>'Purchase order received','purchase_order_id'=>$id],200);
        });
    }

    // POST /api/v1/purchase-orders/{id}/cancel
    public function cancel(Request $request, $id)
    {
        $po = DB::table('purchase_orders')->where('id',$id)->first();
        if (! $po) return response()->json(['message'=>'PO not found'],404);
        if ($po->status === 'received') return response()->json(['message'=>'Cannot cancel a received PO'],400);

        DB::table('purchase_orders')->where('id',$id)->update(['status'=>'cancelled','updated_at'=>now()]);
        return response()->json(['message'=>'Purchase order cancelled'],200);
    }
}
