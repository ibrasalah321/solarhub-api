<?php

namespace Database\Factories;

use App\Models\OrderStore;
use App\Models\Store;
use App\Models\StoreRating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreRating>
 */
class StoreRatingFactory extends Factory
{
    protected $model = StoreRating::class;

    public function definition(): array
    {
        $comments = [
            'المنتجات أصلية ومطابقة للمواصفات.',
            'تغليف ممتاز وسرعة في التسليم.',
            'تعامل جيد وأسعار منافسة.',
            'البضاعة ممتازة مع توفير الضمان.',
        ];

        return [
            'order_store_id' => OrderStore::factory(),

            'customer_id' => User::role('customer')
                ->inRandomOrder()
                ->value('id') ?? User::factory()->customer(),

            'store_id' => Store::query()
                ->inRandomOrder()
                ->value('id') ?? Store::factory(),

            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->randomElement($comments),
            'is_approved' => true,
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }
}
