<?php

namespace Tests\Feature\Authorization;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MasterProduct;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupplierApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }



    public function test_supplier_cannot_update_or_delete_another_stores_product(): void
{
    $owner = $this->createSupplierWithStore('approved');
    $product = $this->createMasterProduct();

    Sanctum::actingAs($owner);

    $this->postJson('/api/store-products', $this->listingData($product))
        ->assertCreated();

    $listingId = (int) \App\Models\StoreProduct::query()
        ->where('store_id', $owner->store->id)
        ->where('master_product_id', $product->id)
        ->value('id');

    $otherSupplier = $this->createSupplierWithStore('approved');
    Sanctum::actingAs($otherSupplier);

    $this->putJson("/api/store-products/{$listingId}", [
        'stock_quantity' => 99,
    ])->assertForbidden();

    $this->deleteJson("/api/store-products/{$listingId}")
        ->assertForbidden();

    $this->assertDatabaseHas('store_products', [
        'id' => $listingId,
        'store_id' => $owner->store->id,
        'stock_quantity' => 5,
    ]);
}

public function test_supplier_can_update_own_store_product(): void
{
    $supplier = $this->createSupplierWithStore('approved');
    $product = $this->createMasterProduct();

    Sanctum::actingAs($supplier);

    $this->postJson('/api/store-products', $this->listingData($product))
        ->assertCreated();

    $listingId = (int) \App\Models\StoreProduct::query()
        ->where('store_id', $supplier->store->id)
        ->where('master_product_id', $product->id)
        ->value('id');

    $this->putJson("/api/store-products/{$listingId}", [
        'stock_quantity' => 8,
    ])->assertOk();

    $this->assertDatabaseHas('store_products', [
        'id' => $listingId,
        'store_id' => $supplier->store->id,
        'stock_quantity' => 8,
    ]);
}

    public function test_pending_supplier_cannot_create_store_product(): void
    {
        $supplier = $this->createSupplierWithStore('pending');
        $product = $this->createMasterProduct();

        Sanctum::actingAs($supplier);

        $this->postJson('/api/store-products', $this->listingData($product))
            ->assertForbidden();

        $this->assertDatabaseCount('store_products', 0);
    }

    public function test_rejected_supplier_cannot_create_store_product(): void
    {
        $supplier = $this->createSupplierWithStore('rejected');
        $product = $this->createMasterProduct();

        Sanctum::actingAs($supplier);

        $this->postJson('/api/store-products', $this->listingData($product))
            ->assertForbidden();

        $this->assertDatabaseCount('store_products', 0);
    }

    public function test_approved_supplier_can_create_store_product(): void
    {
        $supplier = $this->createSupplierWithStore('approved');
        $product = $this->createMasterProduct();

        Sanctum::actingAs($supplier);

        $this->postJson('/api/store-products', $this->listingData($product))
            ->assertCreated();

        $this->assertDatabaseHas('store_products', [
            'store_id' => $supplier->store->id,
            'master_product_id' => $product->id,
            'stock_quantity' => 5,
        ]);
    }

    private function createSupplierWithStore(string $approvalStatus): User
    {
        $supplier = User::factory()->supplier()->create();

        Store::query()->create([
            'user_id' => $supplier->id,
            'company_name' => 'Test Store',
            'store_type' => 'retailer',
            'address_details' => 'Test address',
            'approval_status' => $approvalStatus,
        ]);

        return $supplier;
    }

    private function createMasterProduct(): MasterProduct
    {
        $category = Category::query()->create([
            'name_ar' => 'ألواح شمسية',
            'name_en' => 'Solar Panels',
            'slug' => 'solar-panels',
        ]);

        $brand = Brand::query()->create([
            'name' => 'Test Brand',
        ]);

        return MasterProduct::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'title' => 'Test Solar Panel',
            'is_active' => true,
        ]);
    }

    private function listingData(MasterProduct $product): array
    {
        return [
            'master_product_id' => $product->id,
            'price' => 100,
            'stock_quantity' => 5,
            'min_order_qty' => 1,
        ];
    }
}
