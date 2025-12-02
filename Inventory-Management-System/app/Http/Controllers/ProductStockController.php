<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductStockController extends Controller
{
    // GET /api/product-stocks
    public function index(Request $request)
    {
        $q = ProductStock::with('product','warehouse');

        if ($request->has('product_id')) $q->where('product_id', $request->product_id);
        if ($request->has('warehouse_id')) $q->where('warehouse_id', $request->warehouse_id);
        if ($request->has('low') && $request->low == '1') {
            $q->whereColumn('quantity', '<=', 'reorder_threshold');
        }

        return response()->json($q->orderBy('product_id')->paginate(25));
    }

    // GET /api/product-stocks/{id}
    public function show(ProductStock $productStock)
    {
        $productStock->load('product','warehouse');
        return response()->json($productStock);
    }

    // POST /api/product-stocks
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'=>'required|exists:products,id',
            'warehouse_id'=>'required|exists:warehouses,id',
            'quantity'=>'required|numeric|min:0',
            'reorder_threshold'=>'nullable|numeric|min:0',
            'reorder_qty'=>'nullable|numeric|min:0',
        ]);

        $stock = ProductStock::updateOrCreate(
            ['product_id'=>$data['product_id'],'warehouse_id'=>$data['warehouse_id']],
            [
               'quantity'=>$data['quantity'],
               'reorder_threshold'=>$data['reorder_threshold'] ?? 0,
               'reorder_qty'=>$data['reorder_qty'] ?? 0
            ]
        );

        return response()->json($stock, 201);
    }

    // PATCH /api/product-stocks/{id}/adjust
    public function adjust(Request $request, ProductStock $productStock)
    {
        $data = $request->validate([
            'delta'=>'required|numeric',
            'reason'=>'nullable|string'
        ]);

        return DB::transaction(function() use ($productStock, $data, $request) {
            $old = $productStock->quantity;
            $productStock->quantity = max(0, $old + $data['delta']);
            $productStock->save();

            StockMovement::create([
                'product_id' => $productStock->product_id,
                'from_warehouse_id' => null,
                'to_warehouse_id' => $productStock->warehouse_id,
                'supplier_id' => null,
                'purchase_order_id' => null,
                'quantity' => $data['delta'],
                'type' => 'adjustment',
                'user_id' => $request->user()->id ?? null,
                'notes' => $data['reason'] ?? 'manual adjustment'
            ]);

            return response()->json($productStock);
        });
    }

    // GET /api/product-stocks/low-report
    public function lowStockReport()
    {
        $rows = ProductStock::with('product','warehouse')
            ->whereColumn('quantity','<=','reorder_threshold')
            ->orderBy('quantity')->get();

        return response()->json($rows);
    }
}
