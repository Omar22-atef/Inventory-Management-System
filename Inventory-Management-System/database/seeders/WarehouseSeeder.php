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
            ['name' => 'Main Warehouse', 'location' => 'Cairo'],
            ['name' => 'Secondary Warehouse', 'location' => 'Alexandria'],
            ['name' => 'Remote Warehouse', 'location' => 'Giza'],
        ];

        foreach ($warehouses as $w) {
            Warehouse::create($w);
        }

        $this->command->info('Warehouses seeded: ' . count($warehouses));
    }
}
