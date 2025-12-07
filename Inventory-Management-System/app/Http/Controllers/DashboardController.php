<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Notifications\InventoryAlert;
use App\Models\User;


class DashboardController extends Controller
{
    public function index()
    {
        // Get total counts
        $totalProducts = Product::count();
        $totalOrders   = PurchaseOrder::count();
        $totalStock    = Product::sum('quantity');
        $outOfStock    = Product::where('quantity', 0)->count();

        
        $admin = User::first(); 

        if ($admin) {
            $unreadCount = $admin->unreadNotifications()->count();

            $lowStockAlerts = $admin->unreadNotifications()
                ->where('type', InventoryAlert::class)
                ->get();
        } else {
            $unreadCount    = 0;
            $lowStockAlerts = collect();
        }


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
            'unreadCount',
            'lowStockAlerts',
        ));
    }

    public function getStats()
    {
        $totalProducts = Product::count();
        $totalOrders   = PurchaseOrder::count();
        $totalStock    = Product::sum('quantity');
        $outOfStock    = Product::where('quantity', 0)->count();

        return response()->json([
            'totalProducts' => $totalProducts,
            'totalOrders'   => $totalOrders,
            'totalStock'    => $totalStock,
            'outOfStock'    => $outOfStock,
        ]);
    }
}
