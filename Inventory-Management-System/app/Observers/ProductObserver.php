<?php

namespace App\Observers;

use App\Mail\OrderSupplierMail;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\InventoryAlert;
use Illuminate\Support\Facades\Mail;

class ProductObserver
{
    public function created(Product $product): void
    {
        $this->checkLowStockAndNotify($product, null);
    }

    public function updated(Product $product): void
    {
        $oldQuantity = $product->getOriginal('quantity');
        $this->checkLowStockAndNotify($product, $oldQuantity);
    }

    protected function checkLowStockAndNotify(Product $product, ?int $oldQuantity): void
    {
        $quantity  = $product->quantity;
        $threshold = $product->reorder_threshold ?? 0;

        // not low now → stop
        if ($quantity > $threshold) {
            return;
        }

        // was already low before → avoid duplicate notifications
        if ($oldQuantity !== null && $oldQuantity <= $threshold) {
            return;
        }

        // message used in notification
        $message = "Low stock: {$product->name} ({$quantity} left)";

        // 1) notify the (single) admin user
        $admin = User::first();   // single-admin project

        if ($admin) {
            $admin->notify(new InventoryAlert($product, $quantity, $message, 'admin'));
        }

        // 2) send email to supplier
        $supplier = $product->supplier ?? null;
        if (!$supplier && $product->supplier_id) {
            $supplier = Supplier::find($product->supplier_id);
        }

        if ($supplier && !empty($supplier->email)) {
            Mail::to($supplier->email)->queue(
                new OrderSupplierMail($product, $supplier, $quantity)
            );
        }
    }
}
