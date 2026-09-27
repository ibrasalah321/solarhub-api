<?php

namespace Database\Factories;

use App\Models\EngineerProfile;
use App\Models\EngineerRating;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EngineerRating>
 */
class EngineerRatingFactory extends Factory
{
    protected $model = EngineerRating::class;

    public function definition(): array
    {
        $comments = [
            'مهندس متمكن وملتزم بمواعيد التنفيذ.',
            'تم فحص المنظومة والتأكد من كفاءة التشغيل.',
            'خدمة ممتازة ودراسة دقيقة للاحتياجات.',
            'عمل جيد في اختيار القواطع والكابلات المناسبة.',
        ];

        return [
            'service_request_id' => ServiceRequest::factory()->completed(),

            'customer_id' => User::role('customer')
                ->inRandomOrder()
                ->value('id') ?? User::factory()->customer(),

            'engineer_id' => EngineerProfile::query()
                ->inRandomOrder()
                ->value('id') ?? EngineerProfile::factory(),

            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->randomElement($comments),
            'is_approved' => true,
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }
}
