<?php

namespace App\Http\Controllers;

use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StockController extends Controller
{
    // GET /api/stock (list stock/logs)
    public function index(Request $request)
    {
        // prefer a stock_movements table if present
        if (Schema::hasTable('stock_movements')) {
            $q = DB::table('stock_movements')->orderByDesc('created_at');
            if ($request->has('product_id')) $q->where('product_id', $request->product_id);
            if ($request->has('type')) $q->where('type', $request->type);
            return response()->json($q->paginate(30));
        }

        // fallback to stock_logs history
        if (Schema::hasTable('stock_logs')) {
            $q = DB::table('stock_logs')->orderByDesc('created_at');
            if ($request->has('product_id')) $q->where('product_id', $request->product_id);
            if ($request->has('type')) $q->where('type', $request->type);
            return response()->json($q->paginate(30));
        }

        // final fallback: show products with quantities
        if (Schema::hasTable('products')) {
            $q = DB::table('products')->select('id','name','quantity')->orderBy('id');
            return response()->json($q->paginate(30));
        }

        return response()->json(['message' => 'No stock data available'], 404);
    }

    // Helper: add quantity (use products.quantity or product_stocks if exists)
    protected function incrementQuantity($productId, $qty, $warehouseId = null)
    {
        // If product_stocks table exists, prefer it (warehouse-aware)
        if (Schema::hasTable('product_stocks')) {
            $query = DB::table('product_stocks')->where('product_id', $productId);
            if ($warehouseId !== null) $query->where('warehouse_id', $warehouseId);
            $row = $query->lockForUpdate()->first();

            if ($row) {
                DB::table('product_stocks')->where('id', $row->id)
                    ->update(['quantity' => DB::raw("quantity + ({$qty})"), 'updated_at' => now()]);
                return true;
            }

            DB::table('product_stocks')->insert([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $qty,
                'reorder_threshold' => 0,
                'reorder_qty' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return true;
        }

        // Fallback: update products.quantity
        if (Schema::hasTable('products') && Schema::hasColumn('products','quantity')) {
            $p = DB::table('products')->where('id', $productId)->lockForUpdate()->first();
            if (! $p) return false;

            DB::table('products')->where('id', $productId)
                ->update(['quantity' => DB::raw("GREATEST(0, COALESCE(quantity,0) + ({$qty}))"), 'updated_at' => now()]);

            return true;
        }

        return false;
    }

    // Helper: remove quantity, return true if possible
    protected function decrementQuantity($productId, $qty, $warehouseId = null)
    {
        if (Schema::hasTable('product_stocks')) {
            $query = DB::table('product_stocks')->where('product_id', $productId);
            if ($warehouseId !== null) $query->where('warehouse_id', $warehouseId);
            $row = $query->lockForUpdate()->first();

            if (! $row) return false;
            if ($row->quantity < $qty) return false;

            DB::table('product_stocks')->where('id', $row->id)
                ->update(['quantity' => DB::raw("quantity - ({$qty})"), 'updated_at' => now()]);

            return true;
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products','quantity')) {
            $p = DB::table('products')->where('id', $productId)->lockForUpdate()->first();
            if (! $p) return false;
            if (($p->quantity ?? 0) < $qty) return false;

            DB::table('products')->where('id', $productId)
                ->update(['quantity' => DB::raw("GREATEST(0, quantity - ({$qty}))"), 'updated_at' => now()]);

            return true;
        }

        return false;
    }

    // Helper: write to stock_logs if exists
    protected function maybeLog($productId, $type, $quantity, $note = null)
    {
        if (! Schema::hasTable('stock_logs')) return;
        try {
            if (class_exists(StockLog::class)) {
                StockLog::create([
                    'product_id' => $productId,
                    'type' => $type,
                    'quantity' => $quantity,
                    'note' => $note,
                ]);
            } else {
                DB::table('stock_logs')->insert([
                    'product_id' => $productId,
                    'type' => $type,
                    'quantity' => $quantity,
                    'note' => $note,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            
        }
    }

    // POST /api/stock/receive  - supplier delivery or manual add
    public function receive(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'to_warehouse_id' => 'nullable|integer',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'quantity' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function() use ($data, $request) {
            $ok = $this->incrementQuantity($data['product_id'], $data['quantity'], $data['to_warehouse_id'] ?? null);

            // optional movement table
            if (Schema::hasTable('stock_movements')) {
                DB::table('stock_movements')->insert([
                    'product_id' => $data['product_id'],
                    'from_warehouse_id' => null,
                    'to_warehouse_id' => $data['to_warehouse_id'] ?? null,
                    'supplier_id' => $data['supplier_id'] ?? null,
                    'purchase_order_id' => $data['purchase_order_id'] ?? null,
                    'quantity' => $data['quantity'],
                    'type' => 'supplier_in',
                    'user_id' => $request->user()->id ?? null,
                    'notes' => $data['notes'] ?? 'receive',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->maybeLog($data['product_id'], 'in', $data['quantity'], $data['notes'] ?? 'receive');

            return response()->json(['ok' => $ok], $ok ? 201 : 500);
        });
    }

    // POST /api/stock/transfer  - transfer between warehouses (nullable warehouses supported)
    public function transfer(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'nullable|integer',
            'to_warehouse_id' => 'nullable|integer|different:from_warehouse_id',
            'quantity' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function() use ($data, $request) {
            // decrement source
            $ok = $this->decrementQuantity($data['product_id'], $data['quantity'], $data['from_warehouse_id'] ?? null);
            if (! $ok) return response()->json(['message' => 'Insufficient stock in source'], 400);

            // increment destination
            $this->incrementQuantity($data['product_id'], $data['quantity'], $data['to_warehouse_id'] ?? null);

            if (Schema::hasTable('stock_movements')) {
                DB::table('stock_movements')->insert([
                    'product_id' => $data['product_id'],
                    'from_warehouse_id' => $data['from_warehouse_id'] ?? null,
                    'to_warehouse_id' => $data['to_warehouse_id'] ?? null,
                    'supplier_id' => null,
                    'purchase_order_id' => null,
                    'quantity' => $data['quantity'],
                    'type' => 'transfer',
                    'user_id' => $request->user()->id ?? null,
                    'notes' => $data['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->maybeLog($data['product_id'], 'out', $data['quantity'], 'transfer out');
            $this->maybeLog($data['product_id'], 'in', $data['quantity'], 'transfer in');

            return response()->json(['ok' => true], 201);
        });
    }

    // POST /api/stock/outbound  - remove stock (sale/shipment)
    public function outbound(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'nullable|integer',
            'quantity' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function() use ($data, $request) {
            $ok = $this->decrementQuantity($data['product_id'], $data['quantity'], $data['from_warehouse_id'] ?? null);
            if (! $ok) return response()->json(['message' => 'Insufficient stock'], 400);

            if (Schema::hasTable('stock_movements')) {
                DB::table('stock_movements')->insert([
                    'product_id' => $data['product_id'],
                    'from_warehouse_id' => $data['from_warehouse_id'] ?? null,
                    'to_warehouse_id' => null,
                    'supplier_id' => null,
                    'purchase_order_id' => null,
                    'quantity' => $data['quantity'],
                    'type' => 'outbound',
                    'user_id' => $request->user()->id ?? null,
                    'notes' => $data['notes'] ?? 'outbound',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->maybeLog($data['product_id'], 'out', $data['quantity'], $data['notes'] ?? 'outbound');

            return response()->json(['ok' => true], 201);
        });
    }
}
