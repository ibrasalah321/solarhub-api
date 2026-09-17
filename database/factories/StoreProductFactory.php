<?php

namespace Database\Factories;

use App\Models\StoreProduct;
use App\Models\Store;
use App\Models\MasterProduct;
use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreProductFactory extends Factory
{
    protected $model = StoreProduct::class;

    public function definition(): array
    {
        return [
            'master_product_id' => MasterProduct::inRandomOrder()->value('id') ?? MasterProduct::factory(),
            'store_id'          => Store::inRandomOrder()->value('id') ?? Store::factory(),
            'governorate_id'    => Governorate::inRandomOrder()->value('id') ?? 1,
            'price'             => fake()->randomFloat(2, 50, 3000),
            'stock_quantity'    => fake()->numberBetween(5, 100),
            'min_order_qty'     => 1,
            'warranty_period'   => fake()->randomElement(['1 Year', '2 Years', '5 Years']),
            'is_available'      => true,
            'status'            => 'active',
        ];
    }
}
