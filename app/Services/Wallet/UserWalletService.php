<?php

namespace App\Services\Wallet;

use App\Models\UserWallet;

class UserWalletService
{
    public function createWallet(int $userId, array $data): UserWallet
    {
        if (!empty($data['is_default']) && $data['is_default']) {
            UserWallet::where('user_id', $userId)->update(['is_default' => false]);
        }

        $data['user_id'] = $userId;
        return UserWallet::create($data);
    }

    public function updateWallet(UserWallet $wallet, array $data): UserWallet
    {
        if (!empty($data['is_default']) && $data['is_default']) {
            UserWallet::where('user_id', $wallet->user_id)
                ->where('id', '!=', $wallet->id)
                ->update(['is_default' => false]);
        }

        $wallet->update($data);
        return $wallet;
    }
}