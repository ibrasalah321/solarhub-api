<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserWallet;

class UserWalletSeeder extends Seeder
{
    public function run(): void
    {
        UserWallet::factory()->count(10)->create();
    }
}
