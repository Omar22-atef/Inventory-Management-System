<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
  public function run(): void

    {
        $productIds = Product::pluck('id');
        $warehouseIds = Warehouse::pluck('id');

        if ($productIds->isEmpty() || $warehouseIds->isEmpty()) {
            $this->command->info('No products or warehouses found — skipping StockSeeder.');
            return;
        }

        foreach ($productIds as $productId) {
            foreach ($warehouseIds as $warehouseId) {
                ProductStock::insert([
                    'product_id'        => $productId,
                    'warehouse_id'      => $warehouseId,
                    'quantity'          => rand(10, 100),
                    'reorder_threshold' => rand(5, 20),
                ]);
            }
        }

        $this->command->info('Product stocks seeded.');
    }
}
