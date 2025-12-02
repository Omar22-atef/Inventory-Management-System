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

        if (Category::count() === 0) {
            $default = Category::create(['name' => 'General']);
        } else {
            $default = Category::first();
        }

        $products = [
            ['name' => 'Laptop', 'price' => 15000, 'category_id' => $default->id],
            ['name' => 'Mouse',  'price' => 150,   'category_id' => $default->id],
            ['name' => 'Keyboard','price' => 300,  'category_id' => $default->id],
            ['name' => 'Monitor','price' => 4000,  'category_id' => $default->id],
            ['name' => 'Headphones','price' => 500,'category_id' => $default->id],
            ['name' => 'TV', 'price' => 20000, 'category_id' => $default->id]
        ];

        foreach ($products as $product) {
            Product::insert($product);
        }
    }

}
