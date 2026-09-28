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

    public function definition(): array
    {
        $lng = fake()->longitude(42.5, 48.5);
        $lat = fake()->latitude(13.0, 16.5);

        $capacities = ['3 kW', '5.5 kW', '10 kW', '15 kW', '25 HP', '40 HP', '75 HP'];

        $descriptions = [
            'مطلوب دراسة وتوريد وتركيب منظومة طاقة شمسية متكاملة لتشغيل غطاس مياه زراعي عمق 180 متر.',
            'نحتاج مهندس لتركيب وتشغيل إنفرتر هجين مع بطارية وألواح شمسية.',
            'فحص وصيانة منظومة قائمة بعد توقف الإنفرتر.',
            'مطلوب تصميم مخطط وتوزيع أحمال كهربائية لمنزل.',
        ];

        return [
            'customer_id' => User::role('customer')
                ->inRandomOrder()
                ->value('id') ?? User::factory()->customer(),

            'service_type_id' => ServiceType::query()
                ->inRandomOrder()
                ->value('id') ?? 1,

            'governorate_id' => Governorate::query()
                ->inRandomOrder()
                ->value('id'),

            'system_capacity_estimate' => fake()->randomElement($capacities),
            'location_details' => 'الشارع العام - بجوار '.fake()->company(),
            'description' => fake()->randomElement($descriptions),
            'attachment_file' => fake()->optional(0.6)
                ->passthrough('attachments/'.fake()->uuid().'.pdf'),
            'status' => 'open_for_bids',

            'location_coordinates' => DB::raw(
"extensions.ST_GeographyFromText('SRID=4326;POINT({$lng} {$lat})')"            ),

            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'updated_at' => now(),
        ];
    }

    public function awarded(): static
    {
        return $this->state(fn () => ['status' => 'awarded']);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => ['status' => 'in_progress']);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }
}
