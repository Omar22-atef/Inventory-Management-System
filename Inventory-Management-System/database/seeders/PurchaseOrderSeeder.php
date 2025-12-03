<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $order = PurchaseOrder::create([
            'supplier_id' => 1,
            'status' => 'received',
            'total_price' => 15000,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $order->id,
            'product_id' => 1,
            'quantity' => 5,
            'unit_price' => 3000,
            'total_price' => 15000,
        ]);
    }
}
