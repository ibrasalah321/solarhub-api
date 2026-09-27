<?php

namespace Tests\Feature\Authorization;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MasterProduct;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicStoreVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_only_approved_store_has_a_public_detail_page(): void
    {
        $pending = $this->createStore('pending');
        $rejected = $this->createStore('rejected');
        $approved = $this->createStore('approved');

        $this->getJson("/api/stores/{$pending->id}")->assertNotFound();
        $this->getJson("/api/stores/{$rejected->id}")->assertNotFound();
        $this->getJson("/api/stores/{$approved->id}")->assertOk();
    }

    public function test_public_product_list_and_detail_hide_unapproved_stores(): void
    {
        $product = $this->createMasterProduct();

        $pendingListing = $this->createListing(
            $this->createStore('pending'),
            $product
        );

        $rejectedListing = $this->createListing(
            $this->createStore('rejected'),
            $product
        );

        $approvedListing = $this->createListing(
            $this->createStore('approved'),
            $product
        );

        $this->getJson('/api/store-products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $approvedListing->id);

        $this->getJson("/api/store-products/{$pendingListing->id}")
            ->assertNotFound();

        $this->getJson("/api/store-products/{$rejectedListing->id}")
            ->assertNotFound();

        $this->getJson("/api/store-products/{$approvedListing->id}")
            ->assertOk();
    }

    public function test_inactive_product_has_no_public_detail_page(): void
    {
        $store = $this->createStore('approved');
        $product = $this->createMasterProduct();

        $listing = $this->createListing($store, $product);
        $listing->update(['status' => 'inactive']);

        $this->getJson("/api/store-products/{$listing->id}")
            ->assertNotFound();
    }

    private function createStore(string $approvalStatus): Store
    {
        $supplier = User::factory()->supplier()->create();

        return Store::query()->create([
            'user_id' => $supplier->id,
            'company_name' => 'Test Store '.$supplier->id,
            'store_type' => 'retailer',
            'address_details' => 'Test address',
            'approval_status' => $approvalStatus,
        ]);
    }

    private function createMasterProduct(): MasterProduct
    {
        $category = Category::query()->create([
            'name_ar' => 'ألواح',
            'name_en' => 'Panels',
            'slug' => 'panels',
        ]);

        $brand = Brand::query()->create(['name' => 'Test Brand']);

        return MasterProduct::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'title' => 'Test Panel',
        ]);
    }

    private function createListing(Store $store, MasterProduct $product): StoreProduct
    {
        return StoreProduct::query()->create([
            'store_id' => $store->id,
            'master_product_id' => $product->id,
            'price' => 100,
            'stock_quantity' => 5,
            'min_order_qty' => 1,
            'status' => 'active',
        ]);
    }
}
