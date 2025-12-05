<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
   public function index()
    {
        // $product = Product::all();
        // return response()->json($product, 200);

        $products = Product::with(['supplier', 'category'])->get();
        $suppliers = Supplier::all();
        $categories = Category::all();

        return view('manage-products', compact('products', 'suppliers', 'categories'));
    }


    public function store(CreateProductRequest $request)
    {
        // $product = Product::create($request->validated());
        // return response()->json($product, 201);


    $product = Product::create($request->validated());

    // Load relationships
    $product->load(['supplier', 'category']);

    return response()->json($product, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        // $product = Product::findOrFail($id);
        // $product->update($request->validated());
        // return response()->json($product, 200);
        // dd($product);

        $product = Product::findOrFail($id);
    $product->update($request->validated());

    // Load relationships
    $product->load(['supplier', 'category']);

    return response()->json($product, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json($product, 200);
    }

}
