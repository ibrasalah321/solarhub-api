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

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $storeComments = [
            'المنتجات أصلية ومطابقة تماماً للمواصفات المعروضة في الكتالوج.',
            'تغليف ممتاز وسرعة في تسليم الألواح والبطاريات لمكتب النقل.',
            'تعامل راقي وأسعار منافسة مقارنة بالسوق المحلي.',
            'البضاعة ممتازة مع توفير كرت الضمان المعتمد.',
        ];

        return [
            // ربط التقييم بطلب متجر (order_stores) مع ضمان عدم التكرار
            'order_store_id' => OrderStore::factory(),
            'customer_id' => User::where('user_type', 'customer')->inRandomOrder()->value('id') ?? User::factory()->customer(),
            'store_id' => Store::inRandomOrder()->value('id') ?? Store::factory(),
            
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->randomElement($storeComments),
            'is_approved' => true,
            
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }
}