<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderQuotation;

class OrderQuotationSeeder extends Seeder
{
    public function run(): void
    {
        OrderQuotation::factory()->count(5)->create();
    }
}
