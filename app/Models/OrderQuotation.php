<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderQuotation extends Model
{
    use HasFactory;

    protected $table = 'order_quotations';

    protected $fillable = [
        'order_store_id',
        'total_price',
        'notes',
        'status',
    ];

    public function orderStore(): BelongsTo
    {
        return $this->belongsTo(OrderStore::class, 'order_store_id');
    }
}