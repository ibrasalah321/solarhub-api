<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'order_store_id',
        'store_product_id',
        'unit_price',
        'quantity',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'order_store_id' => 'integer',
            'store_product_id' => 'integer',
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'total_price' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function orderStore(): BelongsTo
    {
        return $this->belongsTo(OrderStore::class, 'order_store_id');
    }

    public function storeProduct(): BelongsTo
    {
        return $this->belongsTo(StoreProduct::class, 'store_product_id');
    }
}