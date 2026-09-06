<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StorePayout;

class StorePayoutSeeder extends Seeder
{
    public function run(): void
    {
        StorePayout::factory()->count(5)->create();
    }
}
