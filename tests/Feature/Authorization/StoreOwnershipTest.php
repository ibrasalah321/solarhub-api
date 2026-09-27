<?php

namespace Tests\Feature\Authorization;

use App\Models\Store;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_cannot_update_store(): void
    {
        $store = $this->createStore();

        $this->putJson("/api/stores/{$store->id}", [
            'bio' => 'Updated description',
        ])->assertUnauthorized();
    }

    public function test_customer_cannot_update_store(): void
    {
        $store = $this->createStore();
        $customer = User::factory()->customer()->create();

        Sanctum::actingAs($customer);

        $this->putJson("/api/stores/{$store->id}", [
            'bio' => 'Updated description',
        ])->assertForbidden();
    }

    public function test_supplier_cannot_update_another_suppliers_store(): void
    {
        $otherSuppliersStore = $this->createStore();
        $supplier = User::factory()->supplier()->create();

        Sanctum::actingAs($supplier);

        $this->putJson("/api/stores/{$otherSuppliersStore->id}", [
            'bio' => 'Changed by another supplier',
        ])->assertForbidden();

        $this->assertDatabaseHas('stores', [
            'id' => $otherSuppliersStore->id,
            'bio' => null,
        ]);
    }

    public function test_supplier_can_update_own_store(): void
    {
        $supplier = User::factory()->supplier()->create();
        $store = $this->createStore($supplier);

        Sanctum::actingAs($supplier);

        $this->putJson("/api/stores/{$store->id}", [
            'bio' => 'Updated by the owner',
        ])->assertOk();

        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'bio' => 'Updated by the owner',
        ]);
    }

    private function createStore(?User $supplier = null): Store
    {
        $supplier ??= User::factory()->supplier()->create();

        return Store::query()->create([
            'user_id' => $supplier->id,
            'company_name' => 'Test Store',
            'store_type' => 'retailer',
            'address_details' => 'Test address',
            'approval_status' => 'approved',
        ]);
    }
}
