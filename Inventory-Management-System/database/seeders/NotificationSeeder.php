<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\StockLog;
use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        $stockLog = StockLog::first();  // ← أهم سطر هنا

        Notification::create([
            'user_id'       => $user->id,
            'stock_log_id'  => $stockLog->id,   // ← بدل 5
            'message'       => 'Low stock alert: Laptop HP',
            'read'          => false,
        ]);
    }
}
