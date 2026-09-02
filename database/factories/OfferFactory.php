<?php

namespace Database\Factories;

use App\Models\EngineerProfile;
use App\Models\Offer;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $proposals = [
            'يتضمن العرض فحص الموقع، حساب زوايا السقوط، توريد وتركيب هيكل التثبيت المجلفن، وتوصيل لوحة الحماية والتأريض الكامل.',
            'دراسة شاملة للأحمال وتوريد إنفرتر معتمد وضبط الإعدادات مع برمجة وضع توفير الطاقة وتدريب العميل على المراقبة عبر التطبيق.',
            'تنفيذ شبكة التوصيلات باستخدام كابلات نحاسية معزولة مقاومة للشمس، تركيب قواطع DC/AC أصلية، وتشغيل تجريبي لمدة 48 ساعة.',
            'العرض يشمل التوريد والتركيب والتشغيل مع ضمان صيانة مجانية لمدة 6 أشهر وتوفير قطع الغيار الأصلية.',
        ];

        return [
            // ربط العرض بطلب خدمة قائم أو إنشاء طلب جديد
            'service_request_id' => ServiceRequest::inRandomOrder()->value('id') ?? ServiceRequest::factory(),
            
            // ربط العرض بمهندس معتمد موجود
            'engineer_id' => EngineerProfile::inRandomOrder()->value('id') ?? EngineerProfile::factory(),
            
            // تكلفة تقديرية بالدولار (بين 150$ للأعمال البسيطة إلى 3500$ للمشاريع الكبيرة)
            'proposed_cost' => fake()->randomFloat(2, 150, 3500),
            
            // مدة التنفيذ بالأيام
            'execution_time_days' => fake()->numberBetween(2, 20),
            
            'technical_proposal' => fake()->randomElement($proposals),
            'proposal_file' => fake()->optional(0.5)->passthrough('proposals/' . fake()->uuid() . '.pdf'),
            
            // الحالة الافتراضية: قيد المراجعة من العميل
            'status' => 'pending',
            
            'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * حالة: عرض مقبول وتمت الترسية عليه
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
        ]);
    }

    /**
     * حالة: عرض مرفوض
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}