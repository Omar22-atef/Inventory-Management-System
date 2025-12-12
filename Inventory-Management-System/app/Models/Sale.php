<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['total_price'];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}

