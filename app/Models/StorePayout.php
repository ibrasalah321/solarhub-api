<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePayout extends Model
{
    use HasFactory;

    protected $table = 'store_payouts';

    protected $fillable = [
        'order_id',
        'store_id',
        'total_amount',
        'platform_commission',
        'net_amount',
        'transfer_status',
        'transfer_reference',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}