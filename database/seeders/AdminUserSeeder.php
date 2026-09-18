<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $govId = DB::table('governorates')->value('id');

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@solarhub.com'],
            [
                'name'                => 'مدير النظام',
                'phone'               => '777000000',
                'password'            => Hash::make('password'),
                'status'              => 'active',
                'governorate_id'      => $govId,
                'default_coordinates' => null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]
        );
    }
}
