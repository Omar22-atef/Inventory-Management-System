<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Product;

class StatsController extends Controller
{
    /**
     * Return totals for dashboard as JSON.
     * Adjust field names if your DB uses other names (e.g. unit_price, qty).
     */
    public function totals(Request $request)
    {
        // Total sales (all time): sum of price * quantity on sales table
        $totalSales = Sale::select(DB::raw('COALESCE(SUM(price * quantity), 0) as total'))->value('total');

        // Sales for selected date (optional: ?date=YYYY-MM-DD)
        $selectedDate = $request->query('date');
        $salesSelectedDate = 0;
        if ($selectedDate) {
            $salesSelectedDate = Sale::whereDate('created_at', $selectedDate)
                ->select(DB::raw('COALESCE(SUM(price * quantity), 0) as total'))
                ->value('total');
        }

        // Sales this month
        $salesThisMonth = Sale::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->select(DB::raw('COALESCE(SUM(price * quantity), 0) as total'))
            ->value('total');

        // Total stock value = sum(cost_price * stock_quantity) on products table
        // If you prefer to use sale price, change cost_price --> sale_price
        $stockValue = Product::select(DB::raw('COALESCE(SUM(cost_price * stock_quantity), 0) as total'))->value('total');

        return response()->json([
            'total_sales' => (float) $totalSales,
            'sales_selected_date' => (float) $salesSelectedDate,
            'sales_this_month' => (float) $salesThisMonth,
            'stock_value' => (float) $stockValue,
        ]);
    }
}
