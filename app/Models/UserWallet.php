<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
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
}