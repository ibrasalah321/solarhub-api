<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. جلب أول معرف محافظة متاح
        $governorateId = DB::table('governorates')->value('id');

        // خط الطول وخط العرض لصنعاء
        $longitude = 44.1910;
        $latitude = 15.3694;

        // 2. إدخال أو تحديث حساب الأدمن
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@solarhub.com'],
            [
                'name' => 'مدير النظام',
                'governorate_id' => $governorateId,
                'phone' => '777000000',
                'password' => Hash::make('Admin@123456'),
                'user_type' => 'admin',
                'address' => 'صنعاء - الإدارة العامة',
                'status' => 'approved',
                // كتابة الإحداثيات الجغرافية بصيغة PostGIS الصحيحة (SRID 4326 القياسي لـ GPS)
                'default_coordinates' => DB::raw("ST_GeographyFromText('SRID=4326;POINT({$longitude} {$latitude})')"),
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}