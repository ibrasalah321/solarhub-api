<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $productIds = StoreProduct::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->pluck('id');

        if ($productIds->isEmpty()) {
            $this->command->warn('لا توجد منتجات نشطة.');

            return;
        }

        $customers = User::role('customer')
            ->orderBy('id')
            ->get();

        $count = min(3, $productIds->count());

        foreach ($customers as $index => $customer) {
            for ($offset = 0; $offset < $count; $offset++) {
                $productId = $productIds[
                    ($index + $offset) % $productIds->count()
                ];

                Favorite::firstOrCreate([
                    'user_id' => $customer->id,
                    'store_product_id' => $productId,
                ]);
            }
        }

        $this->command->info('تمت تعبئة المفضلة ببيانات تجريبية.');
    }
}
