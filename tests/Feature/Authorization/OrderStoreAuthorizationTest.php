<?php

namespace Tests\Feature\Authorization;

use App\Models\Order;
use App\Models\OrderStore;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderStoreAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_unrelated_customer_cannot_view_order_store(): void
    {
        [$orderStore] = $this->createOrderStore();

        Sanctum::actingAs(User::factory()->customer()->create());

        $this->getJson("/api/order-stores/{$orderStore->id}")
            ->assertForbidden();
    }

    public function test_order_customer_can_view_order_store(): void
    {
        [$orderStore, $customer] = $this->createOrderStore();

        Sanctum::actingAs($customer);

        $this->getJson("/api/order-stores/{$orderStore->id}")
            ->assertOk();
    }

    public function test_store_owner_can_view_order_store(): void
    {
        [$orderStore, , $supplier] = $this->createOrderStore();

        Sanctum::actingAs($supplier);

        $this->getJson("/api/order-stores/{$orderStore->id}")
            ->assertOk();
    }

    public function test_other_supplier_cannot_change_order_store_status(): void
    {
        [$orderStore] = $this->createOrderStore();

        $otherSupplier = User::factory()->supplier()->create();
        $this->createStore($otherSupplier, 'approved');
        Sanctum::actingAs($otherSupplier);

        $this->patchJson("/api/order-stores/{$orderStore->id}/status", [
            'status' => 'accepted',
        ])->assertForbidden();

        $this->assertDatabaseHas('order_stores', [
            'id' => $orderStore->id,
            'status' => 'pending',
        ]);
    }

    public function test_pending_store_owner_cannot_change_status(): void
    {
        [$orderStore, , $supplier] = $this->createOrderStore('pending');

        Sanctum::actingAs($supplier);

        $this->patchJson("/api/order-stores/{$orderStore->id}/status", [
            'status' => 'accepted',
        ])->assertForbidden();
    }

    public function test_approved_store_owner_can_change_status(): void
    {
        [$orderStore, , $supplier] = $this->createOrderStore('approved');

        Sanctum::actingAs($supplier);

        $this->patchJson("/api/order-stores/{$orderStore->id}/status", [
            'status' => 'accepted',
        ])->assertOk();

        $this->assertDatabaseHas('order_stores', [
            'id' => $orderStore->id,
            'status' => 'accepted',
        ]);
    }

    private function createOrderStore(string $approvalStatus = 'approved'): array
    {
        $customer = User::factory()->customer()->create();
        $supplier = User::factory()->supplier()->create();
        $store = $this->createStore($supplier, $approvalStatus);

        $order = Order::query()->create([
            'order_number' => 'TEST-'.uniqid(),
            'customer_id' => $customer->id,
            'total_amount' => 100,
            'delivery_address' => 'Test address',
            'status' => 'pending',
        ]);

        $orderStore = OrderStore::query()->create([
            'order_id' => $order->id,
            'store_id' => $store->id,
            'subtotal' => 100,
            'status' => 'pending',
        ]);

        return [$orderStore, $customer, $supplier];
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
