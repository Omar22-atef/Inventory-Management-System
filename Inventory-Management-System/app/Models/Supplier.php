<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
   use HasFactory;

   protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'payment_terms'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'supplier_products')
                    ->withPivot(['supplier_price', 'lead_time_days']);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
