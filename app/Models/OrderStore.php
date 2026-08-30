<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderStore extends Model
{
    use HasFactory;

    protected $table = 'order_stores';

    protected $fillable = [
        'order_id',
        'store_id',
        'subtotal',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'store_id' => 'integer',
            'subtotal' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_store_id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(StoreRating::class, 'order_store_id');
    }
}