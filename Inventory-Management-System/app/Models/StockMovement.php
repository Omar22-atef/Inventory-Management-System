<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id','from_warehouse_id','to_warehouse_id','supplier_id',
        'purchase_order_id','quantity','type','user_id','notes'
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function fromWarehouse() { return $this->belongsTo(Warehouse::class,'from_warehouse_id'); }
    public function toWarehouse() { return $this->belongsTo(Warehouse::class,'to_warehouse_id'); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function user() { return $this->belongsTo(User::class); }
}
