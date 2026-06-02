<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Order model.
 * @property int $id
 * @property int $user_id
 * @property float $total_price
 * @property string $status
 */
class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_price',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
        ];
    }
}
