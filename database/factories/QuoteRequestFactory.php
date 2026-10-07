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
    $offeredPrice = round($targetPrice * 0.95, 2);

    $createdAt = now()->subDays(3);
    $respondedAt = $createdAt->copy()->addDay();
    $acceptedAt = $respondedAt->copy()->addDay();

    return [
        'customer_id' => User::factory(),

        'store_product_id' => StoreProduct::inRandomOrder()
            ->value('id') ?? StoreProduct::factory(),

        'quantity' => $qty,
        'customer_target_price' => $targetPrice,

        'offered_unit_price' => $offeredPrice,
        'total_price' => round($qty * $offeredPrice, 2),

        'status' => 'accepted',
        'customer_notes' => 'طلب عرض سعر تجريبي.',
        'store_notes' => 'عرض تجريبي: السعر المقترح للوحدة حسب الكمية المطلوبة.',

        'responded_at' => $respondedAt,
        'accepted_at' => $acceptedAt,
        'created_at' => $createdAt,
        'updated_at' => $acceptedAt,
    ];
}
}
