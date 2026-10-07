<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class WalletProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers=[
            ['JAWALI','جوالي','Jawali','wallets/jawali.png'],
            ['FLOOSAK','فلوسك','Floosak','wallets/floosak.png'],
            ['ONE_CASH','ون كاش','One Cash','wallets/onecash.png'],
            ['KURAIMI_FLOOS','الكريمي فلوس','Al Kuraimi Floos','wallets/kurimi.png'],
        ];
        foreach($providers as [$code,$nameAr,$nameEn,$logoPath]){
            DB::table('wallet_providers')->updateOrInsert(['code'=>$code],[
                'name_ar'=>$nameAr,'name_en'=>$nameEn,'logo_path'=>$logoPath,
                'is_active'=>true,'updated_at'=>now(),
                'created_at'=>now(),
            ]);
        }
    }
}