<?php

namespace Database\Factories;

use App\Models\Governorate;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lng = fake()->longitude(42.5, 48.5);
        $lat = fake()->latitude(13.0, 16.5);

        $capacities = ['3 kW', '5.5 kW', '10 kW', '15 kW', '25 HP', '40 HP', '75 HP'];

        $descriptions = [
            'مطلوب دراسة وتوريد وتركيب منظومة طاقة شمسية متكاملة لتشغيل غطاس مياه زراعي عمق 180 متر.',
            'نحتاج مهندس لتركيب وتشغيل إنفرتر داي هجين 6 كيلو مع بطارية ليثيوم 100 أمبير و8 ألواح شمسية.',
            'فحص وصيانة منظومة قائمة في مزرعة بعد توقف الإنفرتر وظهور كود خطأ متكرر في فترات الظهيرة.',
            'مطلوب تصميم مخطط وتوزيع أحمال كهربائية لمنزل ريفي مكون من طابقين مع نظام حماية وتأريض.',
            'طلب استشارة فنية ومعاينة مساحة السطح لتركيب منظومة تجارية لمعمل خياطة بقدرة 15 كيلو واط.',
        ];

        return [
            // جلب عميل موجود من نوع customer أو إنشاء حساب عميل جديد تلقائياً
            'customer_id' => User::where('user_type', 'customer')->inRandomOrder()->value('id') ?? User::factory()->customer(),
            'service_type_id' => ServiceType::inRandomOrder()->value('id') ?? DB::table('service_types')->value('id') ?? 1,
            'governorate_id' => Governorate::inRandomOrder()->value('id') ?? DB::table('governorates')->value('id') ?? 1,
            
            'system_capacity_estimate' => fake()->randomElement($capacities),
            'location_details' => 'الشارع العام - بجوار ' . fake()->company(),
            'description' => fake()->randomElement($descriptions),
            'attachment_file' => fake()->optional(0.6)->passthrough('attachments/' . fake()->uuid() . '.pdf'),
            
            // الحالة الافتراضية: مفتوح لاستقبال عروض الأسعار من المهندسين
            'status' => 'open_for_bids',
            
            // إحداثيات PostGIS
            'location_coordinates' => DB::raw("ST_GeographyFromText('SRID=4326;POINT({$lng} {$lat})')"),
            
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * حالة: تم ترسية المشروع على مهندس
     */
    public function awarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'awarded',
        ]);
    }

    /**
     * حالة: المشروع قيد التنفيذ الميداني
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    /**
     * حالة: تم إنجاز المشروع وتسليمه
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * حالة: تم إلغاء الطلب
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}