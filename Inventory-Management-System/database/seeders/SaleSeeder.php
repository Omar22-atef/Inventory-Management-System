<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sale = Sale::create([
            'total_price' => 6000,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => 1,
            'quantity' => 2,
            'unit_price' => 3000,
            'total_price' => 6000,
        ]);
    }
}
