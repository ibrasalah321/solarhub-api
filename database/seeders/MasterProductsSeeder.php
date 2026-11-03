<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MasterProductsSeeder extends Seeder
{
    private const SOURCE_NAME = 'منتجات الطاقة الشمسية المتاحة في السوق اليمني.pdf';

    public function run(): void
    {
        $catalog = json_decode(file_get_contents(database_path('data/solar_catalog_yemen.json')), true);
        if (! is_array($catalog) || count($catalog['records'] ?? []) !== 245) {
            throw new RuntimeException('The reviewed solar catalog must contain exactly 245 records.');
        }
        foreach ($catalog['records'] as $record) {
            $this->seedProduct($record);
        }
    }

    private function seedProduct(array $record): void
    {
        $brandId = $this->referenceId('brands', 'name', $record['brand']);
        $categoryId = $this->referenceId('categories', 'slug', $record['category']);
        DB::table('master_products')->updateOrInsert(['source_key' => $record['source_key']], [
            'category_id' => $categoryId, 'brand_id' => $brandId,
            'title' => mb_substr($record['title'], 0, 255),
            'model_number' => $record['model_number'],
            'datasheet_file' => null, 'is_active' => true,
            'source_name' => self::SOURCE_NAME, 'source_page' => $record['source_page'],
            'source_notes' => $record['source_notes'], 'updated_at' => now(),
            'created_at' => now(),
        ]);
        $productId = DB::table('master_products')->where('source_key', $record['source_key'])->value('id');
        if (! $productId) {
            throw new RuntimeException('Unable to seed catalog record '.$record['source_key']);
        }
        DB::table('master_products')
            ->where('id', $productId)
            ->where(function ($query): void {
                $query->whereNull('description')->orWhere('description', '');
            })
            ->update([
                'description' => 'وصف تجريبي للمنتج: '.$record['title'].' — من العلامة التجارية '.$record['brand'].'. هذه بيانات للاختبار وليست مواصفات فنية معتمدة.',
            ]);
        if ($record['capacity'] !== null && $record['capacity'] !== '') {
            DB::table('product_specifications')->updateOrInsert([
                'product_id' => $productId, 'name' => 'القدرة أو الحجم كما وردت',
            ], [
                'value' => mb_substr($record['capacity'], 0, 255), 'unit' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }

    private function referenceId(string $table, string $column, string $value): int
    {
        $id = DB::table($table)->where($column, $value)->value('id');
        if (! $id) {
            throw new RuntimeException("Missing reference data: {$table}.{$column}={$value}");
        }

        return (int) $id;
    }
}
