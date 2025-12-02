<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('warehouse', WarehouseController::class);
Route::apiResource('supplier', SupplierController::class);
Route::apiResource('category', CategoryController::class);
Route::apiResource('product', ProductController::class);
