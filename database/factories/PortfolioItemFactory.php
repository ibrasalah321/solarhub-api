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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {   
        $lng = fake()->longitude(42.5, 48.5);
        $lat = fake()->latitude(13.0, 16.5);

        $titles = [
            'منظومة ضخ مياه زراعية لبئر ارتوازية',
            'تركيب منظومة طاقة شمسية هجينة لمنزل سكني',
            'مشروع طاقة شمسية لمستشفى ومستوصف ريفي',
            'تغذية مزرعة دواجن بالطاقة الكهروضوئية',
            'منظومة طاقة شمسية تجارية لمصنع ومعمل',
        ];

        $capacities = ['5.5 kW', '10 kW', '15 kW', '25 kW', '40 HP (حصان)', '60 HP (حصان)'];
        $types = ['ضخ مياه', 'منزلي هجين (Hybrid)', 'تجاري', 'منظومة معزولة (Off-Grid)'];
        return [
            'engineer_id' => EngineerProfile::factory(),
            'governorate_id' => Governorate::inRandomOrder()->value('id') ?? DB::table('governorates')->value('id') ?? 1 ,

            'service_type_id' => ServiceType::inRandomOrder()->value('id') ?? DB::table('service_types')->value('id') ?? 1 ,

            'project_title' => fake()->randomElements($titles),
            'project_type' => fake()->randomElements($types),
            'system_capacity' => fake()->randomElements($capacities),
            'image_path' => fake()->imageUrl(800,600,'nature'),
            'file_path' => 'PortfolioItems/' . fake()->uuid() . '.pdf',
            'address_text' => fake()->address(),
            'location_coordinates' => DB::raw("ST_GeographyFromText('SRID=4326;POINT({$lng} {$lat})')"),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }
}
