<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('platform_settings')->updateOrInsert(
            ['id' => 1],
            [
                'platform_name'       => 'سولار هاب - SolarHub',
                'support_phone'       => '777000000',
                'support_email'       => 'support@solarhub.com',
                'logo_path'           => 'settings/logo.png',
                'favicon_path'        => 'settings/favicon.ico',
                'currency'            => 'YER',
                'is_maintenance_mode' => false,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]
        );
    }
}
