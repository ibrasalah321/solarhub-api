<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WalletProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['code' => 'JAWALI', 'name_ar' => 'محفظة جوالي', 'name_en' => 'Jawali', 'logo_path' => 'wallets/jawali.png', 'is_active' => true],
            ['code' => 'FLOOSAK', 'name_ar' => 'محفظة فلوسك', 'name_en' => 'Floosak', 'logo_path' => 'wallets/floosak.png', 'is_active' => true],
            ['code' => 'ONE_CASH', 'name_ar' => 'ون كاش', 'name_en' => 'OneCash', 'logo_path' => 'wallets/onecash.png', 'is_active' => true],
            ['code' => 'KURIMI', 'name_ar' => 'الكريمي إم فلوس', 'name_en' => 'M-Floos', 'logo_path' => 'wallets/kurimi.png', 'is_active' => true],
        ];

        foreach ($providers as $item) {
            DB::table('wallet_providers')->updateOrInsert(
                ['code' => $item['code']],
                array_merge($item, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
