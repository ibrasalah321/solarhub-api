<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletProvider extends Model
{
    use HasFactory;

    protected $table = 'wallet_providers';

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'logo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function userWallets(): HasMany
    {
        return $this->hasMany(UserWallet::class, 'wallet_provider_id');
    }

    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class, 'wallet_provider_id');
    }
}