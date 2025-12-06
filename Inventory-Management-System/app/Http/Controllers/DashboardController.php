<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get total counts
        $totalProducts = Product::count();
        $totalOrders = PurchaseOrder::count();
        $totalStock = Product::sum('quantity');
        $outOfStock = Product::whereColumn('quantity', '<=', 'reorder_threshold')->count();

        // Get products with low stock (below reorder threshold)
        $lowStockProducts = Product::whereColumn('quantity', '<=', 'reorder_threshold')
            ->with('category')
            ->limit(5)
            ->get();

        // Get recent products
        $recentProducts = Product::with('category')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();


        return view('dashboard', compact(
            'totalProducts',
            'totalOrders',
            'totalStock',
            'outOfStock',
            'lowStockProducts',
            'recentProducts',
        ));
    }

    public function getStats()
    {
        // For AJAX requests to update dashboard without reloading
        $totalProducts = Product::count();
        $totalOrders = PurchaseOrder::count();
        $totalStock = Product::sum('quantity');
        $outOfStock = Product::whereColumn('quantity', '<=', 'reorder_threshold')->count();

        return response()->json([
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalStock' => $totalStock,
            'outOfStock' => $outOfStock,
        ]);
    }
}
