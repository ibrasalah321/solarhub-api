<?php

namespace Tests\Feature\Authorization;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MasterProduct;
use App\Models\QuoteRequest;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuoteRequestAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_other_supplier_cannot_respond_to_quote(): void
    {
        [$quote] = $this->createQuote();

        $otherSupplier = User::factory()->supplier()->create();
        $this->createStore($otherSupplier, 'approved');

        Sanctum::actingAs($otherSupplier);

        $this->patchJson("/api/quote-requests/{$quote->id}/respond", [
            'offered_unit_price' => 90,
        ])->assertForbidden();

        $this->assertDatabaseHas('quote_requests', [
            'id' => $quote->id,
            'status' => 'pending',
        ]);
    }

    public function test_pending_supplier_cannot_respond_to_quote(): void
    {
        [$quote, , $supplier] = $this->createQuote('pending');

        Sanctum::actingAs($supplier);

        $this->patchJson("/api/quote-requests/{$quote->id}/respond", [
            'offered_unit_price' => 90,
        ])->assertForbidden();
    }

    public function test_approved_store_owner_can_respond_to_quote(): void
    {
        [$quote, , $supplier] = $this->createQuote();

        Sanctum::actingAs($supplier);

        $this->patchJson("/api/quote-requests/{$quote->id}/respond", [
            'offered_unit_price' => 90,
        ])->assertOk();

        $this->assertDatabaseHas('quote_requests', [
            'id' => $quote->id,
            'status' => 'responded',
        ]);
    }

    public function test_other_customer_cannot_accept_quote(): void
    {
        [$quote] = $this->createQuote();
        $quote->update(['status' => 'responded']);

        Sanctum::actingAs(User::factory()->customer()->create());

        $this->patchJson("/api/quote-requests/{$quote->id}/accept")
            ->assertForbidden();
    }

    public function test_quote_owner_can_accept_response(): void
    {
        [$quote, $customer] = $this->createQuote();
        $quote->update(['status' => 'responded']);

        Sanctum::actingAs($customer);

        $this->patchJson("/api/quote-requests/{$quote->id}/accept")
            ->assertOk();

        $this->assertDatabaseHas('quote_requests', [
            'id' => $quote->id,
            'status' => 'accepted',
        ]);
    }

    private function createQuote(string $approvalStatus = 'approved'): array
    {
        $customer = User::factory()->customer()->create();
        $supplier = User::factory()->supplier()->create();
        $store = $this->createStore($supplier, $approvalStatus);

        $category = Category::query()->create([
            'name_ar' => 'ألواح',
            'name_en' => 'Panels',
            'slug' => 'panels',
        ]);

        $brand = Brand::query()->create(['name' => 'Test Brand']);

        $masterProduct = MasterProduct::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'title' => 'Test Panel',
        ]);

        $storeProduct = StoreProduct::query()->create([
            'store_id' => $store->id,
            'master_product_id' => $masterProduct->id,
            'price' => 100,
            'stock_quantity' => 10,
            'min_order_qty' => 1,
            'status' => 'active',
        ]);

        $quote = QuoteRequest::query()->create([
            'customer_id' => $customer->id,
            'store_product_id' => $storeProduct->id,
            'quantity' => 1,
            'status' => 'pending',
        ]);

        return [$quote, $customer, $supplier];
    }

    private function createStore(User $supplier, string $approvalStatus): Store
    {
        return Store::query()->create([
            'user_id' => $supplier->id,
            'company_name' => 'Test Store',
            'store_type' => 'retailer',
            'address_details' => 'Test address',
            'approval_status' => $approvalStatus,
        ]);
    }
}
