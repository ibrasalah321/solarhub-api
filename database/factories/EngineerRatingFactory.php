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

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $comments = [
            'مهندس متمكن جداً وملتزم بمواعيد التنفيذ وجودة التوصيلات.',
            'شغل نظيف ومرتب، تم فحص المنظومة والتأكد من كفاءة الشحن قبل التسليم.',
            'خدمة ممتازة ودراسة دقيقة لسعة الألواح والبطاريات المناسبة للمنزل.',
            'عمل احترافي وأمانة في اختيار القواطع والكابلات المناسبة.',
            'التنفيذ ممتاز لكن كان هناك تأخير بسيط ليوم واحد في استكمال التركيب.',
        ];

        return [
            // توليد طلب خدمة مكتمل تلقائياً لضمان عدم تكرار الـ UNIQUE constraint
            'service_request_id' => ServiceRequest::factory()->completed(),
            
            // جلب عميل أو توليد عميل جديد
            'customer_id' => User::where('user_type', 'customer')->inRandomOrder()->value('id') ?? User::factory()->customer(),
            
            // جلب مهندس معتمد أو توليد مهندس جديد
            'engineer_id' => EngineerProfile::inRandomOrder()->value('id') ?? EngineerProfile::factory(),
            
            // تقييم من 3 إلى 5 نجوم ليعكس تجارب إيجابية
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->randomElement($comments),
            'is_approved' => true,
            
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }
}