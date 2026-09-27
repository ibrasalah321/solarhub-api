<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderPayment>
 */
class OrderPaymentFactory extends Factory
{
    protected $model = OrderPayment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'wallet_provider_id' => null,
            'amount' => fake()->randomFloat(2, 100, 2000),
            'transaction_reference' => null,
            'receipt_image' => 'receipts/default.jpg',
            'status' => 'pending',
            'verified_by' => null,
            'paid_at' => now(),
            'verified_at' => null,
        ];
    }
}
