<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number'            => 'ORD-' . strtoupper(fake()->bothify('##??####')),
            'customer_id'             => User::factory()->state(['user_type' => 'customer']),
            'delivery_governorate_id' => Governorate::inRandomOrder()->value('id'),
            'total_amount'            => 0, // يتم حسابه تجميعياً
            'delivery_address'        => fake()->address(),
            'payment_method'          => fake()->randomElement(['cash_on_delivery', 'bank_transfer', 'wallet']),
            'status'                  => fake()->randomElement(['pending', 'processing', 'completed', 'cancelled']),
        ];
    }
}
