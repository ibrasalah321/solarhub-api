<?php

namespace Database\Factories;

use App\Models\CartItem;
use App\Models\Cart;
use App\Models\StoreProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'cart_id'          => Cart::factory(),
            'store_product_id' => StoreProduct::factory(),
            'quantity'         => fake()->numberBetween(1, 5),
        ];
    }
}
