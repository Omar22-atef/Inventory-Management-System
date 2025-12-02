<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMoveController extends Controller
{
    // GET /api/stock-moves
    public function index(Request $request)
    {
        $q = StockMovement::with(['product','fromWarehouse','toWarehouse','supplier','user'])->latest();

        if ($request->has('supplier_id')) $q->where('supplier_id', $request->supplier_id);
        if ($request->has('product_id')) $q->where('product_id', $request->product_id);
        if ($request->has('type')) $q->where('type', $request->type);

        return response()->json($q->paginate(30));
    }

    // POST /api/stock-moves/receive  (supplier delivery)
    public function receiveFromSupplier(Request $request)
    {
        $data = $request->validate([
            'product_id'=>'required|exists:products,id',
            'to_warehouse_id'=>'required|exists:warehouses,id',
            'supplier_id'=>'required|exists:suppliers,id',
            'purchase_order_id'=>'nullable|exists:purchase_orders,id',
            'quantity'=>'required|numeric|min:0.0001',
            'notes'=>'nullable|string'
        ]);

        return DB::transaction(function() use ($data, $request) {
            $stock = ProductStock::firstOrCreate(
                ['product_id'=>$data['product_id'],'warehouse_id'=>$data['to_warehouse_id']],
                ['quantity'=>0,'reorder_threshold'=>0,'reorder_qty'=>0]
            );

            $stock = ProductStock::where('id',$stock->id)->lockForUpdate()->first();
            $stock->quantity += $data['quantity'];
            $stock->save();

            $movement = StockMovement::create([
                'product_id'=>$data['product_id'],
                'from_warehouse_id'=>null,
                'to_warehouse_id'=>$data['to_warehouse_id'],
                'supplier_id'=>$data['supplier_id'],
                'purchase_order_id'=>$data['purchase_order_id'] ?? null,
                'quantity'=>$data['quantity'],
                'type'=>'supplier_in',
                'user_id'=>$request->user()->id ?? null,
                'notes'=>$data['notes'] ?? 'supplier delivery'
            ]);

            return response()->json($movement, 201);
        });
    }

    // POST /api/stock-moves/transfer
    public function transfer(Request $request)
    {
        $data = $request->validate([
            'product_id'=>'required|exists:products,id',
            'from_warehouse_id'=>'required|exists:warehouses,id',
            'to_warehouse_id'=>'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity'=>'required|numeric|min:0.0001',
            'notes'=>'nullable|string'
        ]);

        return DB::transaction(function() use ($data, $request) {
            $from = ProductStock::where('product_id',$data['product_id'])
                ->where('warehouse_id',$data['from_warehouse_id'])->lockForUpdate()->first();

            if (!$from || $from->quantity < $data['quantity']) {
                return response()->json(['message'=>'Insufficient stock in source warehouse'], 400);
            }

            $from->quantity -= $data['quantity'];
            $from->save();

            $to = ProductStock::firstOrCreate(
                ['product_id'=>$data['product_id'],'warehouse_id'=>$data['to_warehouse_id']],
                ['quantity'=>0,'reorder_threshold'=>0,'reorder_qty'=>0]
            );
            $to = ProductStock::where('id',$to->id)->lockForUpdate()->first();
            $to->quantity += $data['quantity'];
            $to->save();

            $movement = StockMovement::create([
                'product_id'=>$data['product_id'],
                'from_warehouse_id'=>$data['from_warehouse_id'],
                'to_warehouse_id'=>$data['to_warehouse_id'],
                'supplier_id'=>null,
                'purchase_order_id'=>null,
                'quantity'=>$data['quantity'],
                'type'=>'transfer',
                'user_id'=>$request->user()->id ?? null,
                'notes'=>$data['notes'] ?? null,
            ]);

            return response()->json($movement, 201);
        });
    }

    // POST /api/stock-moves/outbound
    public function outbound(Request $request)
    {
        $data = $request->validate([
            'product_id'=>'required|exists:products,id',
            'from_warehouse_id'=>'required|exists:warehouses,id',
            'quantity'=>'required|numeric|min:0.0001',
            'notes'=>'nullable|string'
        ]);

        return DB::transaction(function() use ($data, $request) {
            $from = ProductStock::where('product_id',$data['product_id'])
                ->where('warehouse_id',$data['from_warehouse_id'])->lockForUpdate()->first();

            if (!$from || $from->quantity < $data['quantity']) {
                return response()->json(['message'=>'Insufficient stock'], 400);
            }

            $from->quantity -= $data['quantity'];
            $from->save();

            $movement = StockMovement::create([
                'product_id'=>$data['product_id'],
                'from_warehouse_id'=>$data['from_warehouse_id'],
                'to_warehouse_id'=>null,
                'supplier_id'=>null,
                'purchase_order_id'=>null,
                'quantity'=>$data['quantity'],
                'type'=>'outbound',
                'user_id'=>$request->user()->id ?? null,
                'notes'=>$data['notes'] ?? 'outbound'
            ]);

            return response()->json($movement, 201);
        });
    }
}
