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
        'order_store_id',
        'user_wallet_id',
        'total_amount',
        'commission_rate',
        'commission_amount',
        'net_amount',
        'status',
        'transfer_reference',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'order_store_id' => 'integer',
            'user_wallet_id' => 'integer',
            'total_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function orderStore(): BelongsTo
    {
        return $this->belongsTo(OrderStore::class, 'order_store_id');
    }

    public function userWallet(): BelongsTo
    {
        return $this->belongsTo(UserWallet::class, 'user_wallet_id');
    }
}