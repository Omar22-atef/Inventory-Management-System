<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'so_number',
        'status',
        'total',
    ];

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }
}
