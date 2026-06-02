<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model representing an item in the shopping cart.
 * 
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property int $quantity
 */
class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
