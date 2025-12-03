<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'stock_log_id',
        'message',
        'read'
    ];

    public function stock_log()
    {
        return $this->belongsTo(StockLog::class);
    }
}

