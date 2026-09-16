<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    use HasFactory;

    protected $table = 'order_payments';

    protected $fillable = [
        'order_id',
        'wallet_provider_id',
        'amount',
        'transaction_reference',
        'receipt_image',
        'status',
        'verified_by',
        'paid_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'wallet_provider_id' => 'integer',
            'amount' => 'decimal:2',
            'verified_by' => 'integer',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function walletProvider(): BelongsTo
    {
        return $this->belongsTo(WalletProvider::class, 'wallet_provider_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}