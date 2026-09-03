<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name_ar' => 'ألواح شمسية', 'name_en' => 'Solar Panels', 'slug' => 'solar-panels', 'icon' => 'solar-panel.svg'],
            ['name_ar' => 'إنفرترات ومحولات', 'name_en' => 'Inverters', 'slug' => 'inverters', 'icon' => 'inverter.svg'],
            ['name_ar' => 'بطاريات', 'name_en' => 'Batteries', 'slug' => 'batteries', 'icon' => 'battery.svg'],
            ['name_ar' => 'منظمات شحن', 'name_en' => 'Charge Controllers', 'slug' => 'charge-controllers', 'icon' => 'controller.svg'],
            ['name_ar' => 'مضخات وغطاسات', 'name_en' => 'Solar Pumps', 'slug' => 'solar-pumps', 'icon' => 'pump.svg'],
            ['name_ar' => 'هياكل وكابلات ومستلزمات', 'name_en' => 'Cables & Structure', 'slug' => 'cables-structure', 'icon' => 'cable.svg'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'parent_id'  => null,
                'name_ar'    => $cat['name_ar'],
                'name_en'    => $cat['name_en'],
                'slug'       => $cat['slug'],
                'icon'       => $cat['icon'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
