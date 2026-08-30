<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreRating extends Model
{
    use HasFactory;

    protected $table = 'store_ratings';

    protected $fillable = [
        'order_store_id',
        'customer_id',
        'store_id',
        'rating',
        'comment',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'order_store_id' => 'integer',
            'customer_id' => 'integer',
            'store_id' => 'integer',
            'rating' => 'integer',
            'is_approved' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function orderStore(): BelongsTo
    {
        return $this->belongsTo(OrderStore::class, 'order_store_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}