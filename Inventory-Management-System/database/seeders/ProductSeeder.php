<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
  public function run(): void
    {

        Product::create([
            'name' => 'Laptop HP',
            'quantity' => 50,
            'price' => 15000,
            'reorder_threshold' => 10,
            'supplier_id' => 1,
            'category_id' => 1,
        ]);

        Product::create([
            'name' => 'Samsung TV',
            'quantity' => 20,
            'price' => 20000,
            'reorder_threshold' => 5,
            'supplier_id' => 1,
            'category_id' => 2,
        ]);

        Product::create([
            'name' => 'Mouse HP',
            'quantity' => 100,
            'price' => 300,
            'reorder_threshold' => 20,
            'supplier_id' => 2,
            'category_id' => 3,
        ]);
    }

}
