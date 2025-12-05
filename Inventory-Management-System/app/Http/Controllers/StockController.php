<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\StockLog;

class StockController extends Controller
{
    // GET /api/v1/stock
    public function index(Request $request)
    {
        if (Schema::hasTable('stock_movements')) {
            $q = DB::table('stock_movements')->orderByDesc('created_at');
            if ($request->has('product_id')) $q->where('product_id', $request->product_id);
            if ($request->has('type')) $q->where('type', $request->type);
            return response()->json($q->paginate(30));
        }

        if (Schema::hasTable('stock_logs')) {
            $q = DB::table('stock_logs')->orderByDesc('created_at');
            if ($request->has('product_id')) $q->where('product_id', $request->product_id);
            if ($request->has('type')) $q->where('type', $request->type);
            return response()->json($q->paginate(30));
        }

        if (Schema::hasTable('products')) {
            $q = DB::table('products')->select('id','name','quantity')->orderBy('id');
            return response()->json($q->paginate(30));
        }

        return response()->json(['message'=>'No stock data available'], 404);
    }

    protected function incrementProductQuantity(int $productId, $qty)
    {
        // prefer product_stocks if exists (not mandatory)
        if (Schema::hasTable('product_stocks')) {
            $row = DB::table('product_stocks')->where('product_id', $productId)->lockForUpdate()->first();
            if ($row) {
                DB::table('product_stocks')->where('id', $row->id)
                    ->update(['quantity' => DB::raw("quantity + ({$qty})"), 'updated_at' => now()]);
                return true;
            }
            DB::table('product_stocks')->insert([
                'product_id'=>$productId,
                'warehouse_id'=>null,
                'quantity'=>$qty,
                'reorder_threshold'=>0,
                'reorder_qty'=>0,
                'created_at'=>now(),
                'updated_at'=>now()
            ]);
            return true;
        }

        // fallback to products.quantity
        if (Schema::hasTable('products') && Schema::hasColumn('products','quantity')) {
            $p = DB::table('products')->where('id', $productId)->lockForUpdate()->first();
            if (! $p) return false;
            DB::table('products')->where('id', $productId)
                ->update(['quantity'=>DB::raw("COALESCE(quantity,0) + ({$qty})"), 'updated_at'=>now()]);
            return true;
        }

        return false;
    }

    protected function decrementProductQuantity(int $productId, $qty)
    {
        if (Schema::hasTable('product_stocks')) {
            $row = DB::table('product_stocks')->where('product_id', $productId)->lockForUpdate()->first();
            if (! $row || $row->quantity < $qty) return false;
            DB::table('product_stocks')->where('id', $row->id)
                ->update(['quantity' => DB::raw("quantity - ({$qty})"), 'updated_at'=>now()]);
            return true;
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products','quantity')) {
            $p = DB::table('products')->where('id', $productId)->lockForUpdate()->first();
            if (! $p || (($p->quantity ?? 0) < $qty)) return false;
            DB::table('products')->where('id', $productId)
                ->update(['quantity' => DB::raw("GREATEST(0, quantity - ({$qty}))"), 'updated_at'=>now()]);
            return true;
        }

        return false;
    }

    protected function logStock($productId, $type, $quantity, $note = null)
    {
        if (! Schema::hasTable('stock_logs')) return;
        try {
            if (class_exists(StockLog::class)) {
                StockLog::create([
                    'product_id'=>$productId,
                    'type'=>$type,
                    'quantity'=>$quantity,
                    'note'=>$note
                ]);
            } else {
                DB::table('stock_logs')->insert([
                    'product_id'=>$productId,
                    'type'=>$type,
                    'quantity'=>$quantity,
                    'note'=>$note,
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);
            }
        } catch (\Throwable $e) {
 
        }
    }

    // POST /api/v1/stock/receive
    public function receive(Request $request)
    {
        $data = $request->validate([
            'product_id'=>'required|exists:products,id',
            'quantity'=>'required|numeric|min:0.0001',
            'supplier_id'=>'nullable|exists:suppliers,id',
            'purchase_order_id'=>'nullable|exists:purchase_orders,id',
            'notes'=>'nullable|string'
        ]);

        return DB::transaction(function() use ($data, $request) {
            $ok = $this->incrementProductQuantity($data['product_id'], $data['quantity']);

            // optional stock_movements table
            if (Schema::hasTable('stock_movements')) {
                DB::table('stock_movements')->insert([
                    'product_id'=>$data['product_id'],
                    'from_warehouse_id'=>null,
                    'to_warehouse_id'=>null,
                    'supplier_id'=>$data['supplier_id'] ?? null,
                    'purchase_order_id'=>$data['purchase_order_id'] ?? null,
                    'quantity'=>$data['quantity'],
                    'type'=>'supplier_in',
                    'user_id'=>$request->user()->id ?? null,
                    'notes'=>$data['notes'] ?? 'receive',
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);
            }

            $this->logStock($data['product_id'],'in',$data['quantity'],$data['notes'] ?? 'receive');

            return response()->json(['ok'=>$ok], $ok?201:500);
        });
    }

    // POST /api/v1/stock/outbound
    public function outbound(Request $request)
    {
        $data = $request->validate([
            'product_id'=>'required|exists:products,id',
            'quantity'=>'required|numeric|min:0.0001',
            'notes'=>'nullable|string'
        ]);

        return DB::transaction(function() use ($data, $request) {
            $ok = $this->decrementProductQuantity($data['product_id'], $data['quantity']);
            if (! $ok) return response()->json(['message'=>'Insufficient stock'], 400);

            if (Schema::hasTable('stock_movements')) {
                DB::table('stock_movements')->insert([
                    'product_id'=>$data['product_id'],
                    'from_warehouse_id'=>null,
                    'to_warehouse_id'=>null,
                    'supplier_id'=>null,
                    'purchase_order_id'=>null,
                    'quantity'=>$data['quantity'],
                    'type'=>'outbound',
                    'user_id'=>$request->user()->id ?? null,
                    'notes'=>$data['notes'] ?? 'outbound',
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);
            }

            $this->logStock($data['product_id'],'out',$data['quantity'],$data['notes'] ?? 'outbound');

            return response()->json(['ok'=>true], 201);
        });
    }

    // POST /api/v1/stock/transfer
    public function transfer(Request $request)
    {
        $data = $request->validate([
            'product_id'=>'required|exists:products,id',
            'quantity'=>'required|numeric|min:0.0001',
            'notes'=>'nullable|string'
        ]);

        // transfer between logical places: decrement then increment (if only products.quantity exists net effect may be 0)
        return DB::transaction(function() use ($data, $request) {
            $ok = $this->decrementProductQuantity($data['product_id'], $data['quantity']);
            if (! $ok) return response()->json(['message'=>'Insufficient stock in source'], 400);

            $this->incrementProductQuantity($data['product_id'], $data['quantity']);

            if (Schema::hasTable('stock_movements')) {
                DB::table('stock_movements')->insert([
                    'product_id'=>$data['product_id'],
                    'from_warehouse_id'=>null,
                    'to_warehouse_id'=>null,
                    'supplier_id'=>null,
                    'purchase_order_id'=>null,
                    'quantity'=>$data['quantity'],
                    'type'=>'transfer',
                    'user_id'=>$request->user()->id ?? null,
                    'notes'=>$data['notes'] ?? 'transfer',
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);
            }

            $this->logStock($data['product_id'],'out',$data['quantity'],'transfer out');
            $this->logStock($data['product_id'],'in',$data['quantity'],'transfer in');

            return response()->json(['ok'=>true], 201);
        });
    }
}
