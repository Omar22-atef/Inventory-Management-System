<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::apiResource('supplier', SupplierController::class);
Route::apiResource('category', CategoryController::class);
Route::apiResource('product', ProductController::class);

use App\Http\Controllers\StockController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    Route::get('purchase-orders', [PurchaseOrderController::class, 'index']);
    Route::get('purchase-orders/{id}', [PurchaseOrderController::class, 'show']);
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store']);
    Route::post('purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive']);
    Route::post('purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel']);
});


Route::prefix('v1')->group(function () {
    Route::post('sales', [SalesController::class, 'store']);
});


Route::prefix('v1')->group(function () {
    Route::get('stock', [StockController::class, 'index']);
    Route::post('stock/receive', [StockController::class, 'receive']);
    Route::post('stock/transfer', [StockController::class, 'transfer']);
    Route::post('stock/outbound', [StockController::class, 'outbound']);
});

// Report API endpoints
Route::get('/reports/sales-inventory', [ReportController::class, 'salesInventory']);
Route::get('/reports/low-stock', [ReportController::class, 'lowStock']);
Route::get('/reports/purchase-history', [ReportController::class, 'purchaseHistory']);
Route::get('/reports/sales-performance', [ReportController::class, 'salesPerformance']);
Route::get('/reports/supplier-performance', [ReportController::class, 'supplierPerformance']);
