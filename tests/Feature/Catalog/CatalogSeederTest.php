<?php

namespace Tests\Feature\Catalog;

use App\Models\MasterProduct;
use Database\Seeders\BrandsSeeder;
use Database\Seeders\CategoriesSeeder;
use Database\Seeders\MasterProductsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_catalog_seeders_are_idempotent_and_keep_links(): void
    {
        $seeders = [CategoriesSeeder::class, BrandsSeeder::class, MasterProductsSeeder::class];
        foreach ($seeders as $seeder) {
            $this->seed($seeder);
        }
        foreach ($seeders as $seeder) {
            $this->seed($seeder);
        }
        $source = json_decode(file_get_contents(database_path('data/solar_catalog_yemen.json')), true);
        $this->assertCount(245, $source['records']);
        $this->assertDatabaseCount('master_products', 245);
        $this->assertDatabaseCount('categories', 12);
        $this->assertDatabaseCount('store_products', 0);
        $this->assertSame(
            collect($source['records'])->whereNotNull('capacity')->where('capacity', '!=', '')->count(),
            \DB::table('product_specifications')->count()
        );
        $this->assertSame(
            245,
            MasterProduct::query()
                ->whereNotNull('source_key')
                ->whereHas('category')
                ->whereHas('brand')
                ->count()
        );
        $jinko = MasterProduct::query()->where('title', 'Jinko Tiger NEO')->firstOrFail();
        $this->assertSame(1, $jinko->source_page);
        $this->assertSame('solar-panels', $jinko->category->slug);
        $this->assertSame('Jinko Solar', $jinko->brand->name);
        $this->assertNull($jinko->model_number);
    }
}
