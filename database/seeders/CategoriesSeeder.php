<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['ألواح شمسية', 'Solar Panels', 'solar-panels', 'solar-panel.svg'],
            ['إنفرترات ومحولات', 'Inverters & Converters', 'inverters', 'inverter.svg'],
            ['بطاريات', 'Batteries', 'batteries', 'battery.svg'],
            ['منظمات شحن', 'Charge Controllers', 'charge-controllers', 'controller.svg'],
            ['مضخات وغطاسات شمسية', 'Solar Pumps & Submersibles', 'solar-pumps', 'pump.svg'],
            ['كابلات وموصلات', 'Cables & Connectors', 'cables-connectors', 'cable.svg'],
            ['سخانات مياه شمسية', 'Solar Water Heaters', 'solar-water-heaters', null],
            ['مكيفات طاقة شمسية', 'Solar Air Conditioners', 'solar-air-conditioners', null],
            ['إنارة وكشافات شمسية', 'Solar Lighting', 'solar-lighting', null],
            ['حماية ومراقبة كهربائية', 'Protection & Monitoring', 'protection-monitoring', null],
            ['مستلزمات تركيب', 'Installation Accessories', 'installation-accessories', null],
            ['شواحن بطاريات', 'Battery Chargers', 'battery-chargers', null],
        ];
        foreach ($categories as [$nameAr,$nameEn,$slug,$icon]) {
            DB::table('categories')->updateOrInsert(['slug' => $slug], [
                'parent_id' => null, 'name_ar' => $nameAr, 'name_en' => $nameEn,
                'icon' => $icon, 'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }
}
