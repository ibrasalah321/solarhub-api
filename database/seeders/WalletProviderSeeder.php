<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WalletProvider;

class WalletProviderSeeder extends Seeder
{
    public function run(): void
    {
        WalletProvider::insert([
            ['name_ar' => 'الكريمي', 'name_en' => 'Kuraimi', 'logo' => 'wallets/kuraimi.png', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name_ar' => 'جيب', 'name_en' => 'Jeeb', 'logo' => 'wallets/jeeb.png', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name_ar' => 'وون كاش', 'name_en' => 'OneCash', 'logo' => 'wallets/onecash.png', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
