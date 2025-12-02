<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InventoryAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public $product;
    public $level;
    public $message;
     public $recipientType; // 'admin' or 'supplier' or other

   /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Product  $product
     * @param  int $level
     * @param  string $message
     * @param  string $recipientType
     */
    public function __construct($product = null, $level = null, $message = null)
    {
        $this->product = $product;
        $this->level = $level;
        $this->message = $message ?? ($product ? "Stock alert for {$product->name}" : 'Inventory alert');
    }

    /**
     * Delivery channels (both database and mail).
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Email notification content.
     */
    public function toMail($notifiable): MailMessage
    {
        $productName = $this->product->name ?? 'Unknown Product';
        $level = $this->level ?? 'N/A';

        // Safe URL (handles missing "products.show" route)
        $url = null;
        try {
            if ($this->product) {
                $url = route('products.show', ['product' => $this->product->id]);
            }
        } catch (\Throwable $e) {
            $url = url('/'); // fallback
        }

        return (new MailMessage)
            ->subject("Inventory Alert: {$productName}")
            ->greeting("Inventory Alert")
            ->line("Product: **{$productName}**")
            ->line("Level: **{$level}**")
            ->line($this->message)
            ->action('View Product', $url)
            ->line('This is an automated stock alert from your inventory system.');
    }

    /**
     * Data stored in the notifications table.
     */
    public function toArray($notifiable): array
    {
        $url = null;
        try {
            if ($this->product) {
                $url = route('products.show', ['product' => $this->product->id]);
            }
        } catch (\Throwable $e) {
            $url = null;
        }

        return [
            'product_id' => $this->product->id ?? null,
            'product_name' => $this->product->name ?? null,
            'level' => $this->level,
            'message' => $this->message,
            'url' => $url,
        ];
    }
}
