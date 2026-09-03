<?php


namespace Database\Factories;

use App\Models\OrderStore;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStoreFactory extends Factory
{
    protected $model = OrderStore::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'store_id' => Store::factory(),
            'subtotal' => 0,
            'status'   => fake()->randomElement(['pending', 'accepted', 'shipped', 'delivered', 'rejected']),
            'notes'    => fake()->sentence(),
        ];
    }
}
