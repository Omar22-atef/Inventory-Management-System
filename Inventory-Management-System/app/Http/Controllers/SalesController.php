<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesController extends Controller
{
    
    public function store(Request $request)
    {
        $data = $request->validate([
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|integer|exists:products,id',
            'items.*.quantity'=>'required|numeric|min:0.0001',
        ]);

        return DB::transaction(function() use ($data, $request) {
            $items = $data['items'];
            $productIds = collect($items)->pluck('product_id')->unique()->values()->all();
            $products = DB::table('products')->whereIn('id', $productIds)->get()->keyBy('id');

            // check availability
            foreach ($items as $it) {
                $pid = $it['product_id'];
                $qty = (float)$it['quantity'];
                $p = $products->get($pid);
                if (! $p) return response()->json(['message'=>"Product {$pid} not found"],404);
                if (($p->quantity ?? 0) < $qty) {
                    return response()->json(['message'=>'Insufficient stock','product_id'=>$pid,'available'=>$p->quantity ?? 0,'requested'=>$qty],400);
                }
            }

            // create sale row
            $saleId = null;
            if (Schema::hasTable('sales')) {
                $saleId = DB::table('sales')->insertGetId([
                    'total_price'=>0,
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);
            }

            $total = 0;
            foreach ($items as $it) {
                $pid = $it['product_id'];
                $qty = (float)$it['quantity'];
                $p = $products->get($pid);

                // determine unit price (if products has price column)
                $unitPrice = property_exists($p,'price') && is_numeric($p->price) ? (float)$p->price : 0.0;
                $lineTotal = $unitPrice * $qty;
                $total += $lineTotal;

                // decrement stock (prefer product_stocks, else products.quantity)
                if (Schema::hasTable('product_stocks')) {
                    $ps = DB::table('product_stocks')->where('product_id',$pid)->lockForUpdate()->first();
                    if ($ps) {
                        if ($ps->quantity < $qty) return response()->json(['message'=>'Insufficient stock in product_stocks','product_id'=>$pid],400);
                        DB::table('product_stocks')->where('id',$ps->id)->update(['quantity'=>DB::raw("quantity - ({$qty})"), 'updated_at'=>now()]);
                    } else {
                        $prodRow = DB::table('products')->where('id',$pid)->lockForUpdate()->first();
                        DB::table('products')->where('id',$pid)->update(['quantity'=>DB::raw("GREATEST(0, quantity - ({$qty}))"), 'updated_at'=>now()]);
                    }
                } else {
                    DB::table('products')->where('id',$pid)->lockForUpdate()->decrement('quantity', $qty);
                }

                // insert sale_items if table exists
                if (Schema::hasTable('sale_items')) {
                    DB::table('sale_items')->insert([
                        'sale_id'=>$saleId,
                        'product_id'=>$pid,
                        'quantity'=>$qty,
                        'unit_price'=>$unitPrice,
                        'total_price'=>$lineTotal,
                        'created_at'=>now(),
                        'updated_at'=>now()
                    ]);
                }

                // log out
                if (Schema::hasTable('stock_logs')) {
                    DB::table('stock_logs')->insert([
                        'product_id'=>$pid,
                        'type'=>'out',
                        'quantity'=>$qty,
                        'note'=>'sale' . ($saleId ? " #{$saleId}" : ''),
                        'created_at'=>now(),
                        'updated_at'=>now()
                    ]);
                }
            }

            // update sale total
            if ($saleId) {
                DB::table('sales')->where('id',$saleId)->update(['total_price'=>$total, 'updated_at'=>now()]);
            }

            return response()->json([
                'sale_id'=>$saleId,
                'total_price'=>$total,
                'items'=>$items
            ], 201);
        });
    }
}
