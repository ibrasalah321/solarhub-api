<?php

namespace Tests\Feature\Authorization;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_cannot_view_or_cancel_another_customers_order(): void
    {
        $order = $this->createOrder();
        $otherCustomer = User::factory()->customer()->create();

        Sanctum::actingAs($otherCustomer);

        $this->getJson("/api/my/orders/{$order->id}")
            ->assertForbidden();

        $this->patchJson("/api/my/orders/{$order->id}/cancel")
            ->assertForbidden();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_customer_can_view_own_order(): void
    {
        $customer = User::factory()->customer()->create();
        $order = $this->createOrder($customer);

        Sanctum::actingAs($customer);

        $this->getJson("/api/my/orders/{$order->id}")
            ->assertOk();
    }

    public function test_customer_can_cancel_own_pending_order(): void
    {
        $customer = User::factory()->customer()->create();
        $order = $this->createOrder($customer);

        Sanctum::actingAs($customer);

        $this->patchJson("/api/my/orders/{$order->id}/cancel")
            ->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    private function createOrder(?User $customer = null): Order
    {
        $customer ??= User::factory()->customer()->create();

        return Order::query()->create([
            'order_number' => 'TEST-'.uniqid(),
            'customer_id' => $customer->id,
            'total_amount' => 100,
            'delivery_address' => 'Test address',
            'status' => 'pending',
        ]);
    }
}
