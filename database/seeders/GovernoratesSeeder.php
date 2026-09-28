<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernoratesSeeder extends Seeder
{
    public function run(): void
    {
        $governorates = [
            [
                'name_ar' => 'صنعاء',
                'name_en' => 'Sanaa',
            ],
            [
                'name_ar' => 'عدن',
                'name_en' => 'Aden',
            ],
            [
                'name_ar' => 'تعز',
                'name_en' => 'Taiz',
            ],
            [
                'name_ar' => 'الحديدة',
                'name_en' => 'Al Hudaydah',
            ],
            [
                'name_ar' => 'حضرموت',
                'name_en' => 'Hadhramaut',
            ],
            [
                'name_ar' => 'إب',
                'name_en' => 'Ibb',
            ],
            [
                'name_ar' => 'ذمار',
                'name_en' => 'Dhamar',
            ],
            [
                'name_ar' => 'مأرب',
                'name_en' => 'Marib',
            ],
        ];

        foreach ($governorates as $governorate) {
            DB::table('governorates')->updateOrInsert(
                [
                    'name_ar' => $governorate['name_ar'],
                ],
                [
                    'name_en' => $governorate['name_en'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
