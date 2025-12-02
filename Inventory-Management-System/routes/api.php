<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\StockMoveController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('warehouse', WarehouseController::class);
Route::apiResource('supplier', SupplierController::class);
Route::apiResource('category', CategoryController::class);
Route::apiResource('product', ProductController::class);

Route::get('product-stocks', [ProductStockController::class,'index']);
Route::post('product-stocks', [ProductStockController::class,'store']);
Route::get('product-stocks/{productStock}', [ProductStockController::class,'show']);
Route::patch('product-stocks/{productStock}/adjust', [ProductStockController::class,'adjust']);
Route::get('product-stocks/low-report', [ProductStockController::class,'lowStockReport']);

Route::get('stock-moves', [StockMoveController::class,'index']);
Route::post('stock-moves/receive', [StockMoveController::class,'receiveFromSupplier']);
Route::post('stock-moves/transfer', [StockMoveController::class,'transfer']);
Route::post('stock-moves/outbound', [StockMoveController::class,'outbound']);
