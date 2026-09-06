<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\User;

class OrderPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::inRandomOrder()->first()?->id ?? 1,
            'payment_method' => $this->faker->randomElement(['الكريمي', 'جيب', 'وون كاش']),
            'amount' => $this->faker->randomFloat(2, 100, 2000),
            'receipt_image' => 'receipts/default.jpg',
            'status' => $this->faker->randomElement(['pending', 'verified', 'rejected']),
            'verified_by' => User::where('user_type', 'admin')->inRandomOrder()->first()?->id,
        ];
    }
}
