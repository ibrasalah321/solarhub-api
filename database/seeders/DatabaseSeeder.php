<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\EngineerProfile;
use App\Models\EngineerCertificate;
use App\Models\Specialization;
use App\Models\Governorate;
use App\Models\PortfolioItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // -------------------------------------------------------------
        // المرحلة 1: تشغيل البيانات الثابتة (Reference Data)
        // -------------------------------------------------------------
        $this->call([
            SpecializationsSeeder::class,
            ServiceTypesSeeder::class,
            AdminUserSeeder::class,
            
        ]);

        // -------------------------------------------------------------
        // المرحلة 2: إنشاء عملاء وهميين (Customers)
        // -------------------------------------------------------------
        User::factory()->count(10)->customer()->create();

        // -------------------------------------------------------------
        // المرحلة 3: إنشاء مهندسين بملفاتهم الشخصية وشهاداتهم وتخصصاتهم
        // -------------------------------------------------------------
        
        // جلب جميع معرّفات التخصصات المتاحة
        $specializationIds = DB::table('specializations')->pluck('id')->toArray();

        // إنشاء 5 مهندسين
        $engineers = EngineerProfile::factory()->count(5)->create();

        foreach ($engineers as $engineer) {
            // 1. توليد من 1 إلى 3 شهادات لكل مهندس
            EngineerCertificate::factory()->count(fake()->numberBetween(1, 3))->create([
                'engineer_id' => $engineer->id,
            ]);
            // توليد مشاريع سابقة في معرض الأعمال لكل مهندس
            PortfolioItem::factory()->count(fake()->numberBetween(1, 3))->create([
                'engineer_id' => $engineer->id,
            ]);

            // 2. ربط المهندس بتخصصين عشوائيين في جدول engineer_specializations
            if (!empty($specializationIds)) {
                $randomSpecs = fake()->randomElements($specializationIds, fake()->numberBetween(1, 3));
                foreach ($randomSpecs as $specId) {
                    DB::table('engineer_specializations')->updateOrInsert(
                        [
                            'engineer_id' => $engineer->id,
                            'specialization_id' => $specId,
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}