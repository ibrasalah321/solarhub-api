<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BrandsSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = json_decode(file_get_contents(database_path('data/solar_catalog_yemen.json')), true);
        if (! is_array($catalog) || ! isset($catalog['records'])) {
            throw new RuntimeException('Invalid solar catalog source data.');
        }
        $names = collect($catalog['records'])->pluck('brand')->filter()->unique(fn (string $name) => mb_strtolower($name));
        foreach ($names as $name) {
            DB::table('brands')->updateOrInsert(['name' => $name], [
                'is_active' => true, 'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }
}
