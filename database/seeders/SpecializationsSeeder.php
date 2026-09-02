<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Specialization; // تأكد من استيراد الموديل الخاص بك هنا
use Illuminate\Support\Facades\DB;

class SpecializationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specializations = [
            [
                'name' => 'فني كهرباء',
                'description' => 'تركيب وصيانة الشبكات الكهربائية المنزلية والصناعية، وفحص التوصيلات وتحديد الأعطال وتصليحها.'
            ],
            [
                'name' => 'مهندس طاقة متجددة',
                'description' => 'تصميم ودراسة أنظمة الطاقة الشمسية وطاقة الرياح، وحساب الأحمال وسعات الألواح والبطاريات والإنفرترات.'
            ],
            [
                'name' => 'هندسة كهرباء',
                'description' => 'تخطيط وتطوير الأنظمة الكهربائية عالية ومنخفضة الجهد، وتصميم لوحات التوزيع وأنظمة التحكم والـ ATS.'
            ],
            [
                'name' => 'هندسة اتصالات',
                'description' => 'تصميم وتشغيل شبكات الاتصالات السلكية واللاسلكية، وأنظمة نقل البيانات، والألياف الضوئية، وحماية الإشارات من التداخل.'
            ],
            [
                'name' => 'تمديدات كهربائية',
                'description' => 'تأسيس وتمديد الكابلات والمواسير الكهربائية في المباني والمنشآت وفق المخططات الهندسية ومعايير السلامة.'
            ],
        ];
        foreach($specializations as $specialization){
            DB::table('specializations')->updateOrInsert(
                ['name' => $specialization['name']],
                [
                    'description' => $specialization['description'],
                    'create_at' =>now(),
                    'update_at' => now(),
                ]
            );
        }
    }
}