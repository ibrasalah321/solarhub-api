<?php

namespace Tests\Feature\Authorization;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderPaymentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_cannot_view_payment(): void
    {
        $payment = $this->createPayment();

        $this->getJson("/api/order_payments/{$payment->id}")
            ->assertUnauthorized();
    }

    public function test_customer_cannot_view_another_customers_payment(): void
    {
        $payment = $this->createPayment();
        $otherCustomer = User::factory()->customer()->create();

        Sanctum::actingAs($otherCustomer);

        $this->getJson("/api/order_payments/{$payment->id}")
            ->assertForbidden();
    }

    public function test_customer_can_view_payment_for_own_order(): void
    {
        $customer = User::factory()->customer()->create();
        $payment = $this->createPayment($customer);

        Sanctum::actingAs($customer);

        $this->getJson("/api/order_payments/{$payment->id}")
            ->assertOk();
    }

    public function test_customer_cannot_verify_payment(): void
    {
        $customer = User::factory()->customer()->create();
        $payment = $this->createPayment($customer);

        Sanctum::actingAs($customer);

        $this->patchJson("/api/order_payments/{$payment->id}/status", [
            'status' => 'verified',
        ])->assertForbidden();

        $this->assertDatabaseHas('order_payments', [
            'id' => $payment->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_verify_payment(): void
    {
        $payment = $this->createPayment();
        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/order_payments/{$payment->id}/status", [
            'status' => 'verified',
        ])->assertOk();

        $this->assertDatabaseHas('order_payments', [
            'id' => $payment->id,
            'status' => 'verified',
            'verified_by' => $admin->id,
        ]);
    }

    private function createPayment(?User $customer = null): OrderPayment
    {
        $customer ??= User::factory()->customer()->create();

        $order = Order::query()->create([
            'order_number' => 'TEST-'.uniqid(),
            'customer_id' => $customer->id,
            'total_amount' => 100,
            'delivery_address' => 'Test address',
            'status' => 'pending',
        ]);

        return OrderPayment::query()->create([
            'order_id' => $order->id,
            'amount' => 100,
            'status' => 'pending',
        ]);
    }
}
