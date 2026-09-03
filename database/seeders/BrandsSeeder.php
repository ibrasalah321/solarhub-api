<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandsSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Jinko Solar', 'website' => 'https://jinkosolar.com'],
            ['name' => 'LONGi', 'website' => 'https://longi.com'],
            ['name' => 'Trina Solar', 'website' => 'https://trinasolar.com'],
            ['name' => 'Deye', 'website' => 'https://deyeinverter.com'],
            ['name' => 'Growatt', 'website' => 'https://growatt.com'],
            ['name' => 'Pylontech', 'website' => 'https://pylontech.com.cn'],
            ['name' => 'Felicity', 'website' => 'https://felicitysolar.com'],
            ['name' => 'Must', 'website' => 'https://mustpower.com'],
            ['name' => 'Voltronic', 'website' => 'https://voltronicpower.com'],
            ['name' => 'RAGGIE', 'website' => 'https://raggie.com'],
            ['name' => 'Eastman', 'website' => 'https://eastman.com'],
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->insert([
                'name'       => $brand['name'],
                'logo'       => 'brands/' . strtolower(str_replace(' ', '_', $brand['name'])) . '.png',
                'website'    => $brand['website'],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
