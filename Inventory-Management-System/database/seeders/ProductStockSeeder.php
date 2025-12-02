<?php

namespace Database\Seeders;

use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_stocks')->truncate();
        $ProductStocks = [
            ['product_id' => 1, 'warehouse_id' => 1, 'quantity' => 20, 'reorder_threshold' => 5],
            ['product_id' => 2, 'warehouse_id' => 1, 'quantity' => 10, 'reorder_threshold' => 3],
            ['product_id' => 3, 'warehouse_id' => 2, 'quantity' => 15, 'reorder_threshold' => 5],
            ['product_id' => 4, 'warehouse_id' => 2, 'quantity' => 25, 'reorder_threshold' => 7],
            ['product_id' => 5, 'warehouse_id' => 1, 'quantity' => 25, 'reorder_threshold' => 7],
            ['product_id' => 6, 'warehouse_id' => 3, 'quantity' => 10, 'reorder_threshold' => 3]
        ];

        foreach($ProductStocks as $product)
        {
            ProductStock::create($product);
        }
    }
}
