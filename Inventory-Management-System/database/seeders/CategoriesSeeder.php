<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Category::create(['name' => 'Smart-Phones', 'description' => 'Tablets, Phones, Ipads']);
        Category::create(['name' => 'Electric-Devices', 'description' => 'TV, Microwave, Washing-Machine']);
        Category::create(['name' => 'Accessories', 'description' => 'Mouse, Kayboard, USB']);
    }
}
