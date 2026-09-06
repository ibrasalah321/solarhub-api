<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OrderStore;

class OrderQuotationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_store_id' => OrderStore::inRandomOrder()->first()?->id ?? 1,
            'total_price' => $this->faker->randomFloat(2, 50, 1000),
            'notes' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'accepted', 'rejected']),
        ];
    }
}
