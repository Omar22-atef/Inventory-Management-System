<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::create([
            'name' => 'TechSource Ltd',
            'email' => 'support@techsource.com',
            'phone' => '01000123344',
            'address' => 'Cairo'
        ]);

        Supplier::create([
            'name' => 'FreshMarket LLC',
            'email' => 'info@freshmarket.com',
            'phone' => '01055667788',
            'address' => 'Alexandria'
        ]);

        Supplier::create([
            'name' => 'Elswidi',
            'email' => 'info@Elswidi.com',
            'phone' => '01055669922',
            'address' => 'Cairo'
        ]);


    }
}
