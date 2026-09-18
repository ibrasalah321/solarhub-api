<?php

namespace Database\Factories;

use App\Models\ProductImage;
use App\Models\MasterProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition(): array
    {
        return [
            'product_id' => MasterProduct::inRandomOrder()->first()?->id ?? MasterProduct::factory(),
            'image_path' => 'products/gallery/img_' . fake()->numberBetween(1, 20) . '.png',
            'is_featured' => fake()->boolean(20),
        ];
    }
}
