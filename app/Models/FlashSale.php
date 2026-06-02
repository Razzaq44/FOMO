<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    protected $fillable = [
        'product_id',
        'flash_sale_price',
        'flash_sale_stock',
        'start_time',
        'end_time',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
