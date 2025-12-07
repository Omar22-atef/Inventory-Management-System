<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderSupplierMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    
    public Product $product;
    public Supplier $supplier;
    public int $currentQuantity;

    /**
     * Create a new message instance.
     */
    public function __construct(Product $product, Supplier $supplier, int $currentQuantity)
    {
        $this->product          = $product;
        $this->supplier         = $supplier;
        $this->currentQuantity = $currentQuantity;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Reorder Request: ' . $this->product->name)
                    ->view('emails.order_supplier')
                    ->with([
                        'product'         => $this->product,
                        'supplier'        => $this->supplier,
                        'currentQuantity'=> $this->currentQuantity,
                    ]);
    }
}
