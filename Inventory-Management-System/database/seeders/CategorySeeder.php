<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics & Computing', 'description' => 'Labtops, Monitors, Desktops'],
            ['name' => 'Mobile & Smart Devices', 'description' => 'Mobiles, Tablets, Smart Watches'],
            ['name' => 'Home Appliances', 'description' => 'Washing Machine, TV, Air Conditioner'],
        ];

        foreach($categories as $category)
        {
            Category::insert($category);
        }
    }
}
