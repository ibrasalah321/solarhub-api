<?php

namespace Database\Seeders;

use App\Models\StorePayout;
use Illuminate\Database\Seeder;

class StorePayoutSeeder extends Seeder
{
    public function run(): void
    {
        StorePayout::factory()->count(5)->create();
    }
}
