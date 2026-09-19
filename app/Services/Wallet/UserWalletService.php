<?php

namespace App\Services\Wallet;

use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserWalletService
{
    /**
     * Get all wallets belonging to the authenticated user.
     */
    public function getMyWallets(User $user): Collection
    {
        return $user->userWallets()
            ->with('walletProvider')
            ->latest()
            ->get();
    }

    /**
     * Create a new wallet for the authenticated user.
     */
    public function createWallet(User $user, array $data): UserWallet
    {
        return DB::transaction(function () use ($user, $data) {
            $isFirstWallet = ! $user->userWallets()->exists();

            $wallet = $user->userWallets()->create([
                'wallet_provider_id' => $data['wallet_provider_id'],
                'account_number' => $data['account_number'],
                'account_name' => $data['account_name'] ?? null,
                'is_default' => $isFirstWallet ? true : (bool) ($data['is_default'] ?? false),
                'is_active' => true,
            ]);

            if ($wallet->is_default) {
                $this->clearOtherDefaults($user, $wallet);
            }

            return $wallet->load('walletProvider');
        });
    }

    /**
     * Update an existing wallet owned by the authenticated user.
     */
    public function updateWallet(User $user, UserWallet $wallet, array $data): UserWallet
    {
        $this->ensureOwnership($user, $wallet);

        return DB::transaction(function () use ($user, $wallet, $data) {
            $wallet->update($data);

            if (($data['is_default'] ?? false) === true) {
                $this->clearOtherDefaults($user, $wallet);
            }

            return $wallet->load('walletProvider');
        });
    }

    /**
     * Delete a wallet owned by the authenticated user.
     * If the deleted wallet was the default one, promote the next active wallet.
     */
    public function deleteWallet(User $user, UserWallet $wallet): void
    {
        $this->ensureOwnership($user, $wallet);

        abort_if(
            $wallet->payouts()->exists(),
            409,
            'This wallet has payout records and cannot be deleted.'
        );

        DB::transaction(function () use ($user, $wallet) {
            $wasDefault = $wallet->is_default;

            $wallet->delete();

            if ($wasDefault) {
                $nextWallet = $user->userWallets()
                    ->where('is_active', true)
                    ->first();

                $nextWallet?->update(['is_default' => true]);
            }
        });
    }

    private function clearOtherDefaults(User $user, UserWallet $wallet): void
    {
        $user->userWallets()
            ->where('id', '!=', $wallet->id)
            ->update(['is_default' => false]);
    }

    private function ensureOwnership(User $user, UserWallet $wallet): void
    {
        abort_unless(
            $wallet->user_id === $user->id,
            403,
            'You are not allowed to manage this wallet.'
        );
    }
}
