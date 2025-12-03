<?php

namespace Database\Seeders;

use App\Models\StockLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StocklogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        StockLog::create([
            'product_id' => 1,
            'type' => 'in',
            'quantity' => 5,
            'note' => 'Initial stock'
        ]);

        StockLog::create([
            'product_id' => 1,
            'type' => 'out',
            'quantity' => 2,
            'note' => 'Sale'
        ]);
    }
}
