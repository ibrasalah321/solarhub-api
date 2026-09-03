<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernoratesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $governorates = [
            ['name_ar' => 'صنعاء', 'name_en' => 'Sanaa', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'عدن', 'name_en' => 'Aden', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'تعز', 'name_en' => 'Taiz', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'الحديدة', 'name_en' => 'Al Hudaydah', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'حضرموت', 'name_en' => 'Hadhramaut', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'إب', 'name_en' => 'Ibb', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'ذمار', 'name_en' => 'Dhamar', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'مأرب', 'name_en' => 'Marib', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('governorates')->insert($governorates);
    }
}
