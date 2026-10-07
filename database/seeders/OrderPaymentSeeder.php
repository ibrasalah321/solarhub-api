<?php

namespace Database\Seeders;

use App\Models\OrderPayment;
use Illuminate\Database\Seeder;

class OrderPaymentSeeder extends Seeder
{
    public function run(): void
    {
        OrderPayment::factory()->count(5)->create();
    }
}
