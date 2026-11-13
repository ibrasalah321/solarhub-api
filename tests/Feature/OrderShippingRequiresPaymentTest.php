<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderStore;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * كل الطلبات مدفوعة مسبقاً: المورد يقبل الطلب ويجهّزه قبل الدفع، لكن لا يشحن إلا بعد
 * أن تغطي الدفعات المعتمدة (verified) إجمالي الطلب الرئيسي.
 */
class OrderShippingRequiresPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $supplier;
    private User $customer;
    private Order $order;
    private OrderStore $orderStore;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->customer = User::factory()->customer()->create();
        $this->supplier = User::factory()->supplier()->create();
        $store = Store::query()->create([
            'user_id' => $this->supplier->id,
            'company_name' => 'Test Store',
            'store_type' => 'retailer',
            'address_details' => 'Test address',
            'approval_status' => 'approved',
        ]);

        $this->order = Order::query()->create([
            'order_number' => 'TEST-'.uniqid(),
            'customer_id' => $this->customer->id,
            'total_amount' => 1000,
            'delivery_address' => 'Test address',
            'status' => 'pending',
        ]);
        $this->orderStore = OrderStore::query()->create([
            'order_id' => $this->order->id,
            'store_id' => $store->id,
            'subtotal' => 1000,
            'status' => 'accepted',
        ]);

        Sanctum::actingAs($this->supplier);
    }

    private function pay(float $amount, string $status): OrderPayment
    {
        return OrderPayment::query()->create([
            'order_id' => $this->order->id,
            'amount' => $amount,
            'receipt_image' => 'orders/test/receipt.png',
            'status' => $status,
        ]);
    }

    private function ship()
    {
        return $this->patchJson("/api/order-stores/{$this->orderStore->id}/status", ['status' => 'shipped']);
    }

    public function test_supplier_can_accept_before_payment(): void
    {
        $this->orderStore->update(['status' => 'pending']);

        $this->patchJson("/api/order-stores/{$this->orderStore->id}/status", ['status' => 'accepted'])
            ->assertOk();
    }

    public function test_shipping_before_any_payment_is_rejected(): void
    {
        $this->ship()
            ->assertStatus(422)
            ->assertJsonPath('message', 'لا يمكن شحن الطلب قبل أن يدفع العميل وتعتمد الإدارة الدفعة.');

        $this->assertSame('accepted', $this->orderStore->fresh()->status);
    }

    public function test_shipping_while_receipt_is_under_review_is_rejected(): void
    {
        $this->pay(1000, 'pending');

        $this->ship()->assertStatus(422);
        $this->assertStringContainsString('قيد المراجعة', $this->ship()->json('message'));
        $this->assertSame('accepted', $this->orderStore->fresh()->status);
    }

    public function test_partial_verified_payment_is_not_enough(): void
    {
        $this->pay(400, 'verified');

        $this->ship()->assertStatus(422);
        $this->assertStringContainsString('لا تغطي إجمالي الطلب', $this->ship()->json('message'));
        $this->assertSame('accepted', $this->orderStore->fresh()->status);
    }

    public function test_rejected_payment_does_not_count(): void
    {
        $this->pay(1000, 'rejected');

        $this->ship()->assertStatus(422);
        $this->assertSame('accepted', $this->orderStore->fresh()->status);
    }

    public function test_shipping_after_payment_is_verified_is_allowed(): void
    {
        $this->pay(1000, 'verified');

        $this->ship()->assertOk()->assertJsonPath('data.status', 'shipped');
        $this->assertSame('shipped', $this->orderStore->fresh()->status);
    }

    public function test_several_verified_payments_that_cover_the_total_are_allowed(): void
    {
        $this->pay(600, 'verified');
        $this->pay(400, 'verified');

        $this->ship()->assertOk();
    }

    public function test_supplier_sees_payment_status_in_store_orders(): void
    {
        $this->pay(1000, 'pending');

        $this->getJson('/api/my/store-orders')
            ->assertOk()
            ->assertJsonPath('data.0.order.payment.status', 'under_review');
    }

    public function test_admin_rejection_notifies_the_customer(): void
    {
        $this->seed(\Database\Seeders\NotificationTemplatesSeeder::class);
        $payment = $this->pay(1000, 'pending');
        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/order_payments/{$payment->id}/status", ['status' => 'rejected'])->assertOk();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->customer->id,
            'template_code' => 'payment_rejected_customer',
        ]);
    }

    public function test_customer_order_shows_payment_summary_and_can_resubmit_after_rejection(): void
    {
        $this->pay(1000, 'rejected');
        Sanctum::actingAs($this->customer);

        $this->getJson("/api/my/orders/{$this->order->id}")
            ->assertOk()
            ->assertJsonPath('data.payment.status', 'unpaid')
            ->assertJsonPath('data.payment.last_rejected', true);

        // الدفعة المرفوضة لا تُحسب، فيمكن رفع إيصال جديد بكامل المبلغ
        $this->assertSame(1000.0, $this->order->fresh()->paymentSummary()['remaining']);
    }
}
