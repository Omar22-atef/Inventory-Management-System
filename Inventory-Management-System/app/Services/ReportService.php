<?php
namespace App\Services;

use App\Models\Product;
use App\Models\StockLog;
use App\Models\SaleItem;
use App\Models\PurchaseOrder;

use Illuminate\Support\Str;

class ReportService
{
    /**
     * Sales & Inventory report
     */
   public function salesInventory()
{
    try {
        $products = Product::with(['supplier', 'category', 'stockLogs'])->get();

        return $products->map(function ($product) {
            // Ensure stockLogs is always a collection
            $stockLogs = $product->stockLogs ?? collect();

            $inputQty = $stockLogs->where('type','in')->sum('quantity');

            // Compute output quantity using sales + any outbound stock logs
            $outputQty = $this->calculateOutputQty($product);

            $stock = $inputQty - $outputQty;

            $cost = $product->cost ?? 0;
            $price = $product->price ?? 0;
            $profit = ($price - $cost) * $outputQty;

            return [
                'id' => $product->id,
                'name' => $product->name ?? '-',
                'category' => $product->category?->name ?? '-',
                'supplier' => $product->supplier?->name ?? '-',
                'inputQty' => $inputQty,
                'outputQty' => $outputQty,
                'calculatedStock' => $stock,
                'inputCost' => $cost,
                'salePrice' => $price,
                'profit' => $profit,
                'reorderThreshold' => $product->reorder_threshold ?? 0,
            ];
        })->values();
    } catch (\Exception $e) {
        \Log::error('ReportService::salesInventory error: ' . $e->getMessage());
        return [];
    }
}

    /**
     * Low-stock report
     */
    public function lowStock()
    {
        $products = Product::with(['supplier', 'stockLogs'])->get();

        return $products->filter(function ($product) {
            $stockLogs = $product->stockLogs ?? collect();
            $inputQty = $stockLogs->where('type','in')->sum('quantity');
            $outputQty = $this->calculateOutputQty($product);
            $stock = $inputQty - $outputQty;
            return $stock <= $product->reorder_threshold;
        })->map(function ($product) {
            $stockLogs = $product->stockLogs ?? collect();
            $inputQty = $stockLogs->where('type','in')->sum('quantity');
            $outputQty = $this->calculateOutputQty($product);
            $stock = $inputQty - $outputQty;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'supplier' => $product->supplier?->name ?? '-',
                'stock' => $stock,
                'reorderThreshold' => $product->reorder_threshold,
            ];
        })->values();
    }

    /**
     * Purchase history report
     */
    public function purchaseHistory()
    {
        $purchases = PurchaseOrder::with(['supplier', 'items.product'])->get();

        return $purchases->map(function ($po) {
            return [
                'id' => $po->id,
                'supplier' => $po->supplier?->name ?? '-',
                'date' => $po->created_at->format('Y-m-d'),
                'items' => $po->items->map(fn($i) => [
                    'product' => $i->product?->name ?? '-',
                    'qty' => $i->quantity,
                    'price' => $i->unit_price,
                    'total' => $i->quantity * $i->unit_price,
                ]),
                'totalAmount' => $po->items->sum(fn($i) => $i->quantity * $i->unit_price),
            ];
        });
    }

    /**
     * Sales performance report
     */
    public function salesPerformance()
    {
        $sales = \App\Models\Sale::with('items')->get();

        return $sales->map(function ($sale) {
            $totalAmount = $sale->items->sum(fn($i) => $i->quantity * $i->unit_price);
            $totalProfit = $sale->items->sum(fn($i) => ($i->unit_price - ($i->product?->cost ?? 0)) * $i->quantity);

            return [
                'id' => $sale->id,
                'date' => $sale->created_at->format('Y-m-d'),
                'items' => $sale->items->map(fn($i) => [
                    'product' => $i->product?->name ?? '-',
                    'qty' => $i->quantity,
                    'price' => $i->unit_price,
                    'total' => $i->quantity * $i->unit_price,
                    'profit' => ($i->unit_price - ($i->product?->cost ?? 0)) * $i->quantity,
                ]),
                'totalAmount' => $totalAmount,
                'totalProfit' => $totalProfit,
            ];
        });
    }

    /**
     * Supplier performance report
     */
    public function supplierPerformance()
    {
        $products = Product::with(['supplier', 'stockLogs'])->get();
        $grouped = $products->groupBy(fn($p) => $p->supplier?->name ?? 'Unknown');

        return $grouped->map(function ($products, $supplierName) {
            $totalInput = $products->sum(fn($p) => $p->stockLogs->where('type','in')->sum('quantity'));
            $totalOutput = $products->sum(fn($p) => $this->calculateOutputQty($p));

            return [
                'supplier' => $supplierName,
                'productsCount' => $products->count(),
                'totalInput' => $totalInput,
                'totalOutput' => $totalOutput,
            ];
        })->values();
    }

    /**
     * Helper to compute output quantity for a product.
     * Sums sale items and stock logs that represent outbound movements.
     */
    private function calculateOutputQty($product)
    {
        $stockLogs = $product->stockLogs ?? collect();

        $stockOutQty = $stockLogs->filter(function ($log) {
            $type = strtolower($log->type ?? '');
            return str_contains($type, 'out') || str_contains($type, 'sale') || str_contains($type, 'sell');
        })->sum('quantity');

        $saleItemsQty = SaleItem::where('product_id', $product->id)->sum('quantity');

        return $stockOutQty + $saleItemsQty;
    }

}
