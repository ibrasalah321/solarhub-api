<?php

namespace Database\Factories;

use App\Models\EngineerProfile;
use App\Models\Governorate;
use App\Models\PortfolioItem;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<PortfolioItem>
 */
class PortfolioItemFactory extends Factory
{
    public function definition(): array
    {
        $longitude = fake()->longitude(42.5, 48.5);
        $latitude = fake()->latitude(13.0, 16.5);

        $titles = [
            'منظومة ضخ مياه زراعية لبئر ارتوازية',
            'تركيب منظومة طاقة شمسية هجينة لمنزل سكني',
            'مشروع طاقة شمسية لمستشفى ومستوصف ريفي',
            'تغذية مزرعة دواجن بالطاقة الكهروضوئية',
            'منظومة طاقة شمسية تجارية لمصنع ومعمل',
        ];

        $capacities = [
            '5.5 kW',
            '10 kW',
            '15 kW',
            '25 kW',
            '40 HP (حصان)',
            '60 HP (حصان)',
        ];

        return [
            'engineer_id' => EngineerProfile::factory(),

            'governorate_id' => Governorate::query()
                ->inRandomOrder()
                ->value('id'),

            'service_type_id' => ServiceType::query()
                ->inRandomOrder()
                ->value('id'),

            'project_title' => fake()
                ->randomElement($titles),

            'system_capacity' => fake()
                ->randomElement($capacities),

            'description' => fake()->paragraph(3),

            'image_path' => fake()
                ->imageUrl(800, 600, 'nature'),

            'file_path' => 'PortfolioItems/'
                .fake()->uuid()
                .'.pdf',

            'address_text' => fake()->address(),

                        'location_coordinates' => DB::raw(
                "extensions.ST_GeographyFromText(
                    'SRID=4326;POINT({$longitude} {$latitude})'
                )"
            ),
            'created_at' => fake()
                ->dateTimeBetween('-6 months', 'now'),

            'updated_at' => now(),
        ];
    }
}
