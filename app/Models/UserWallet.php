<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserWallet extends Model
{
    use HasFactory;

    protected $table = 'user_wallets';

    protected $fillable = [
        'user_id',
        'wallet_provider_id',
        'account_number',
        'account_name',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'wallet_provider_id' => 'integer',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function walletProvider(): BelongsTo
    {
        return $this->belongsTo(WalletProvider::class, 'wallet_provider_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(StorePayout::class, 'user_wallet_id');
    }
}