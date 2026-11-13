<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Governorate;
use App\Models\MasterProduct;
use App\Models\ServiceType;
use App\Models\Specialization;
use App\Models\WalletProvider;
use App\Support\PublicCatalogCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicCatalogCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_is_served_from_cache_until_a_model_changes(): void
    {
        Governorate::query()->create(['name_ar' => 'صنعاء', 'name_en' => 'Sanaa', 'is_active' => true]);

        $this->getJson('/api/governorates')->assertOk()->assertJsonCount(1, 'data');

        // تغيير لا يمر عبر الموديل: يجب أن يبقى الرد المخزّن كما هو
        DB::table('governorates')->insert(['name_ar' => 'عدن', 'name_en' => 'Aden', 'is_active' => true]);
        $this->getJson('/api/governorates')->assertOk()->assertJsonCount(1, 'data');

        // إضافة عبر الموديل (saved): يُحذف الكاش ويظهر العنصرين الجديدين
        Governorate::query()->create(['name_ar' => 'تعز', 'name_en' => 'Taiz', 'is_active' => true]);
        $this->getJson('/api/governorates')->assertOk()->assertJsonCount(3, 'data');

        // الحذف (deleted) يحذف الكاش أيضاً
        Governorate::query()->where('name_en', 'Taiz')->first()->delete();
        $this->getJson('/api/governorates')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_every_catalog_model_flushes_its_list_on_save_and_delete(): void
    {
        $cases = [
            PublicCatalogCache::GOVERNORATES => fn () => Governorate::query()->create(['name_ar' => 'إب', 'name_en' => 'Ibb']),
            PublicCatalogCache::SPECIALIZATIONS => fn () => Specialization::query()->create(['name' => 'Spec', 'is_active' => true]),
            PublicCatalogCache::SERVICE_TYPES => fn () => ServiceType::query()->create(['name' => 'Install', 'is_active' => true]),
            PublicCatalogCache::BRANDS => fn () => Brand::query()->create(['name' => 'Brand A', 'is_active' => true]),
            PublicCatalogCache::CATEGORIES => fn () => Category::query()->create(['name_ar' => 'ألواح', 'name_en' => 'Panels', 'slug' => 'panels']),
            PublicCatalogCache::WALLET_PROVIDERS => fn () => WalletProvider::query()->create(['code' => 'JAWALI', 'name_ar' => 'جوالي', 'is_active' => true]),
        ];

        foreach ($cases as $group => $create) {
            $this->primeAllVariants($group);
            $model = $create();
            $this->assertFlushed($group, "saved should flush {$group}");

            $this->primeAllVariants($group);
            $model->delete();
            $this->assertFlushed($group, "deleted should flush {$group}");
        }
    }

    public function test_master_product_changes_flush_brand_and_category_lists(): void
    {
        $brand = Brand::query()->create(['name' => 'Brand B', 'is_active' => true]);
        $category = Category::query()->create(['name_ar' => 'بطاريات', 'name_en' => 'Batteries', 'slug' => 'batteries']);

        $this->primeAllVariants(PublicCatalogCache::BRANDS);
        $this->primeAllVariants(PublicCatalogCache::CATEGORIES);

        MasterProduct::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'title' => 'Battery 200Ah',
            'model_number' => 'B-200',
            'is_active' => true,
        ]);

        $this->assertFlushed(PublicCatalogCache::BRANDS, 'brand product counts must refresh');
        $this->assertFlushed(PublicCatalogCache::CATEGORIES, 'category product counts must refresh');
    }

    public function test_inactive_items_are_cached_separately_from_the_public_list(): void
    {
        Specialization::query()->create(['name' => 'Active', 'is_active' => true]);
        Specialization::query()->create(['name' => 'Hidden', 'is_active' => false]);

        $this->getJson('/api/specializations')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/specializations?all=1')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/specializations')->assertOk()->assertJsonCount(1, 'data');
    }

    private function primeAllVariants(string $group): void
    {
        foreach (['default', 'all'] as $variant) {
            Cache::put(PublicCatalogCache::key($group, $variant), ['stale'], PublicCatalogCache::TTL_SECONDS);
        }
    }

    private function assertFlushed(string $group, string $message): void
    {
        foreach (['default', 'all'] as $variant) {
            $this->assertFalse(Cache::has(PublicCatalogCache::key($group, $variant)), "{$message} ({$variant})");
        }
    }
}
