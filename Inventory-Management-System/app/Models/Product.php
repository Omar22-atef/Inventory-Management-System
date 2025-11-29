<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'name',
        'price',
        'description',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function ProductStock()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function StockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
