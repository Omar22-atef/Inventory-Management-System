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
        $suppliers = [
            ['name' => 'Amr Saad', 'phone' => '01010521446', 'email' => 'Amr.Saad@gmail.com', 'address' => 'Nasr City', 'payment_terms' => 'Cash'],
            ['name' => 'Mohamed Kareem', 'phone' => '01117771562', 'email' => 'Mohamed.Kareem@gmail.com', 'address' => 'Nasr City', 'payment_terms' => 'Cash'],
            ['name' => 'Omar Ahmed', 'phone' => '01225648910', 'email' => 'Omar.Ahmed@gmail.com', 'address' => 'Nasr City', 'payment_terms' => 'Cash']
        ];

        foreach($suppliers as $supplier)
        {
            Supplier::insert($supplier);
        }
    }
}
