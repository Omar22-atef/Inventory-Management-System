<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run()
    {
        $this->call([
        UserSeeder::class,
        CategoriesSeeder::class,
        SupplierSeeder::class,
        ProductSeeder::class,
        PurchaseOrderSeeder::class,
        SaleSeeder::class,
        StockLogSeeder::class,
        NotificationSeeder::class,
    ]);
    }
}
