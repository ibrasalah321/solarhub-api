<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderPayment;

class OrderPaymentSeeder extends Seeder
{
    public function run(): void
    {
        OrderPayment::factory()->count(5)->create();
    }
}
