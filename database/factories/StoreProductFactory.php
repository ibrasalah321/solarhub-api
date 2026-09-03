<?php

namespace Database\Factories;

use App\Models\StoreProduct;
use App\Models\Store;
use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class StoreProductFactory extends Factory
{
    protected $model = StoreProduct::class;

    public function definition(): array
    {
        $masterProductId = DB::table('master_products')->inRandomOrder()->value('id');
        $storeId = DB::table('stores')->inRandomOrder()->value('id') ?? Store::factory();
        $govId = DB::table('governorates')->inRandomOrder()->value('id');

        return [
            'master_product_id' => $masterProductId,
            'store_id'          => $storeId,
            'governorate_id'    => $govId,
            'price'             => fake()->randomFloat(2, 50, 3000),
            'stock_quantity'    => fake()->numberBetween(5, 100),
            'min_order_qty'     => 1,
            'warranty_period'   => fake()->randomElement(['1 Year', '2 Years', '5 Years', '10 Years']),
            'is_available'      => true,
            'status'            => 'active',
        ];
    }
}
