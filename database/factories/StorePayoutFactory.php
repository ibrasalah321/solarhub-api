<?php

namespace Database\Factories;

use App\Models\OrderStore;
use App\Models\StorePayout;
use App\Models\UserWallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorePayout>
 */
class StorePayoutFactory extends Factory
{
    protected $model = StorePayout::class;

    public function definition(): array
    {
        $totalAmount = fake()->randomFloat(2, 200, 1500);
        $commissionRate = 5.00;
        $commissionAmount = round(
            $totalAmount * ($commissionRate / 100),
            2
        );
        $netAmount = round(
            $totalAmount - $commissionAmount,
            2
        );

        $status = fake()->randomElement([
            'pending',
            'completed',
            'failed',
        ]);

        return [
            'order_store_id' => OrderStore::factory(),

            'user_wallet_id' => UserWallet::query()
                ->inRandomOrder()
                ->value('id') ?? UserWallet::factory(),

            'total_amount' => $totalAmount,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'net_amount' => $netAmount,
            'status' => $status,

            'transfer_reference' => $status === 'completed'
                ? 'TRX-'.strtoupper(fake()->bothify('??########'))
                : null,

            'paid_at' => $status === 'completed'
                ? now()
                : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => 'pending',
            'transfer_reference' => null,
            'paid_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'completed',
            'transfer_reference' =>
                'TRX-'.strtoupper(fake()->bothify('??########')),
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'failed',
            'transfer_reference' => null,
            'paid_at' => null,
        ]);
    }
}
