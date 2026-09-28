<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MasterProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'brand' => 'Jinko Solar',
                'category' => 'solar-panels',
                'title' => 'ألواح جينكو 620 وات Tiger Neo',
                'model_number' => 'JKM620N-78HL4-BDV',
                'description' =>
                    'لوح شمسي N-Type عالي الكفاءة ومقاوم للظروف القاسية',
                'datasheet_file' => 'datasheets/jinko_620w.pdf',
                'image_path' => 'products/jinko_620w.png',
                'specifications' => [
                    [
                        'name' => 'القدرة القصوى',
                        'value' => '620',
                        'unit' => 'W',
                    ],
                    [
                        'name' => 'نوع الخلية',
                        'value' => 'N-Type Monocrystalline',
                        'unit' => null,
                    ],
                ],
            ],
            [
                'brand' => 'Deye',
                'category' => 'inverters',
                'title' => 'انفرتر دايا 6 كيلو وات هجين',
                'model_number' => 'SUN-6K-SG04LP1-EU',
                'description' =>
                    'إنفرتر هجين يدعم البطاريات والشبكة والمولد',
                'datasheet_file' => 'datasheets/deye_6kw.pdf',
                'image_path' => 'products/deye_6kw.png',
                'specifications' => [
                    [
                        'name' => 'القدرة الاسمية',
                        'value' => '6',
                        'unit' => 'kW',
                    ],
                ],
            ],
            [
                'brand' => 'Pylontech',
                'category' => 'batteries',
                'title' => 'بطارية بايلون تيك ليثيوم US5000',
                'model_number' => 'US5000',
                'description' =>
                    'بطارية ليثيوم فوسفات الحديد طويلة العمر',
                'datasheet_file' => 'datasheets/pylontech_us5000.pdf',
                'image_path' => 'products/pylontech_us5000.png',
                'specifications' => [
                    [
                        'name' => 'السعة التخزينية',
                        'value' => '4.8',
                        'unit' => 'kWh',
                    ],
                ],
            ],
        ];

        foreach ($products as $product) {
            $this->seedProduct($product);
        }
    }

    private function seedProduct(array $product): void
    {
        $brandId = $this->referenceId(
            table: 'brands',
            column: 'name',
            value: $product['brand']
        );

        $categoryId = $this->referenceId(
            table: 'categories',
            column: 'slug',
            value: $product['category']
        );

        DB::table('master_products')->updateOrInsert(
            [
                'brand_id' => $brandId,
                'model_number' => $product['model_number'],
            ],
            [
                'category_id' => $categoryId,
                'title' => $product['title'],
                'description' => $product['description'],
                'datasheet_file' => $product['datasheet_file'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $productId = DB::table('master_products')
            ->where('brand_id', $brandId)
            ->where('model_number', $product['model_number'])
            ->value('id');

        if ($productId === null) {
            throw new RuntimeException(
                'Unable to create or locate master product: '
                .$product['model_number']
            );
        }

        DB::table('product_images')->updateOrInsert(
            [
                'product_id' => $productId,
                'image_path' => $product['image_path'],
            ],
            [
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ($product['specifications'] as $specification) {
            DB::table('product_specifications')->updateOrInsert(
                [
                    'product_id' => $productId,
                    'name' => $specification['name'],
                ],
                [
                    'value' => $specification['value'],
                    'unit' => $specification['unit'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function referenceId(
        string $table,
        string $column,
        string $value
    ): int {
        $id = DB::table($table)
            ->where($column, $value)
            ->value('id');

        if ($id === null) {
            throw new RuntimeException(
                "Missing reference data: {$table}.{$column}={$value}"
            );
        }

        return (int) $id;
    }
}
