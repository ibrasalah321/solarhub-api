<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number'            => 'ORD-' . strtoupper(fake()->bothify('##??####')),
            'customer_id'             => User::factory(),
            'delivery_governorate_id' => DB::table('governorates')->inRandomOrder()->value('id') ?? 1,
            'total_amount'            => fake()->randomFloat(2, 100, 5000),
            'delivery_address'        => fake()->address(),
            'status'                  => fake()->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'delivery_coordinates'    => null,
        ];
    }
}
