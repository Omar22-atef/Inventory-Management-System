<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Notification::create([
            'stock_log_id' => 2,
            'message' => 'Low stock alert: Laptop HP',
            'read' => false,
        ]);
    }
}
