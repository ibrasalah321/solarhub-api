<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequest extends Model
{
    use HasFactory;

    protected $table = 'quote_requests';

    protected $fillable = [
        'customer_id',
        'store_product_id',
        'quantity',
        'customer_target_price',
        'offered_unit_price',
        'total_price',
        'status',
        'customer_notes',
        'store_notes',
        'responded_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'store_product_id' => 'integer',
            'quantity' => 'integer',
            'customer_target_price' => 'decimal:2',
            'offered_unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'responded_at' => 'datetime',
            'accepted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function storeProduct(): BelongsTo
    {
        return $this->belongsTo(StoreProduct::class, 'store_product_id');
    }
}