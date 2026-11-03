<?php

namespace Database\Seeders;

use App\Models\OrderQuotation;
use Illuminate\Database\Seeder;

class OrderQuotationSeeder extends Seeder
{
    public function run(): void
    {
        OrderQuotation::factory()->count(5)->create();
    }
}
