<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\OrderStore;
use App\Models\StoreProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 4);
        $unitPrice = fake()->randomFloat(2, 50, 500);

        return [
            'order_store_id'   => OrderStore::factory(),
            'store_product_id' => StoreProduct::factory(),
            'unit_price'       => $unitPrice,
            'quantity'         => $qty,
            'total_price'      => $qty * $unitPrice,
        ];
    }
}
