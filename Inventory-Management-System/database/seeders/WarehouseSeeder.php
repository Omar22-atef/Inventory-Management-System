<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
      public function run(): void
    {
        $warehouses = [
            ['name' => 'Main Warehouse', 'address' => 'Cairo'],
            ['name' => 'Secondary Warehouse', 'address' => 'Alexandria'],
            ['name' => 'Remote Warehouse', 'address' => 'Giza'],
        ];

        foreach ($warehouses as $w) {
            Warehouse::insert($w);
        }

        $this->command->info('Warehouses seeded: ' . count($warehouses));
    }
}
