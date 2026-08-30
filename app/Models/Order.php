<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'customer_id',
        'delivery_governorate_id',
        'total_amount',
        'delivery_address',
        'payment_method',
        'status',
        'delivery_coordinates',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'delivery_governorate_id' => 'integer',
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'delivery_governorate_id');
    }

    public function orderStores(): HasMany
    {
        return $this->hasMany(OrderStore::class, 'order_id');
    }
}