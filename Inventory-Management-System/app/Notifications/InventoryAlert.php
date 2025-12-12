<?php

namespace App\Notifications;

use App\Models\Product;
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
    public function __construct(Product $product, int $level, string $message, string $recipientType = 'admin')
    {
        $this->product       = $product;
        $this->level         = $level;
        $this->message       = $message;
        $this->recipientType = $recipientType;
    }
    /**
     * Delivery channels (both database and mail).
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Email notification content.
     */
   public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Stock Alert: ' . $this->product->name)
            ->line($this->message)
            ->action('View Product', url('/products/' . $this->product->id));
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
            'quantity'          => $this->product->quantity,
            'reorder_threshold' => $this->product->reorder_threshold,
            'level' => $this->level,
            'message' => $this->message,
            'url' => $url,
        ];
    }
}
