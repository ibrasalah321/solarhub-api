<?php

namespace Database\Seeders;

use App\Models\UserWallet;
use Illuminate\Database\Seeder;

class UserWalletSeeder extends Seeder
{
    public function run(): void
    {
        UserWallet::factory()->count(10)->create();
    }
}
