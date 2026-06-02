<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * FlashSale model.
 * @property int $id
 * @property int $product_id
 * @property float $flash_sale_price
 * @property int $flash_sale_stock
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon $end_time
 * @property \Illuminate\Support\Carbon $created_at
 */
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
