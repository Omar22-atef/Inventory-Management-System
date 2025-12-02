<?php

namespace App\Observers;

use App\Mail\OrderSupplierMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Product;
use App\Models\User;
use App\Notifications\InventoryAlert;


class ProductObserver
{
    public function updated(Product $product)
    {
        if ($product->isDirty('stock')) {
            $stock = $product->stock;
            $threshold = $product->reorder_level ?? 5;

            if ($stock <= $threshold) {
                $message = "Low stock: {$product->name} (qty: {$stock})";
                $admins = User::where('is_admin', true)->get(); // adjust predicate

                foreach ($admins as $admin) {
                    $admin->notify(new InventoryAlert($product, $stock, $message,'admin'));
                }
            }
        }
    }
}
