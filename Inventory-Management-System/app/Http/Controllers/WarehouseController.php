<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{

    public function index()
    {
        $warehouse = Warehouse::all();
        return response()->json($warehouse, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WarehouseRequest $request)
    {
        $warehouse = Warehouse::create($request->validated());
        return response()->json($warehouse, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        return response()->json($warehouse, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update($request->only('name', 'address'));
        return response()->json($warehouse, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        if ($warehouse->ProductStocks()->count() > 0) {
            return response()->json([
                'error' => 'Warehouse cannot be deleted, stock exists'
            ], 400);
        }

        $warehouse->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
