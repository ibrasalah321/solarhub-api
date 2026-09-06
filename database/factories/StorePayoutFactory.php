<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\Store;

class StorePayoutFactory extends Factory
{
    public function definition(): array
    {
        $total = $this->faker->randomFloat(2, 200, 1500);
        $commission = $total * 0.05;
        $net = $total - $commission;

        return [
            'order_id' => Order::inRandomOrder()->first()?->id ?? 1,
            'store_id' => Store::inRandomOrder()->first()?->id ?? 1,
            'total_amount' => $total,
            'platform_commission' => $commission,
            'net_amount' => $net,
            'transfer_status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'transfer_reference' => $this->faker->uuid(),
        ];
    }
}
