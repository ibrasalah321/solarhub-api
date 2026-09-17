<?php

namespace Database\Factories;

use App\Models\QuoteRequest;
use App\Models\User;
use App\Models\StoreProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteRequestFactory extends Factory
{
    protected $model = QuoteRequest::class;

    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 10);
        $targetPrice = fake()->randomFloat(2, 100, 1500);

        return [
            'customer_id'           => User::factory(),
            'store_product_id'      => StoreProduct::inRandomOrder()->value('id') ?? StoreProduct::factory(),
            'quantity'              => $qty,
            'customer_target_price' => $targetPrice,
            'offered_unit_price'    => null,
            'total_price'           => null,
            'status'                => 'pending',
            'customer_notes'        => fake()->sentence(),
        ];
    }
}
