<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStore;
use App\Models\Store;
use App\Models\StorePayout;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\WalletProvider;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * زر «إنشاء مستحقات» في مالية المدير: قائمة الطلبات المؤهلة، والإنشاء، وإعادة المحاولة بعد الفشل.
 */
class AdminCreatePayoutTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $supplier;
    private Store $store;
    private UserWallet $wallet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->syncRoles(['admin']);
        $this->supplier = User::factory()->supplier()->create();
        $this->store = Store::query()->create([
            'user_id' => $this->supplier->id,
            'company_name' => 'Test Store',
            'store_type' => 'retailer',
            'address_details' => 'Test address',
            'approval_status' => 'approved',
        ]);
        $provider = WalletProvider::query()->create(['code' => 'test', 'name_ar' => 'محفظة اختبار']);
        $this->wallet = UserWallet::query()->create([
            'user_id' => $this->supplier->id,
            'wallet_provider_id' => $provider->id,
            'account_number' => '777123456',
            'account_name' => 'Supplier',
            'is_default' => true,
            'is_active' => true,
        ]);
        Sanctum::actingAs($this->admin);
    }

    private function orderStore(string $status, float $subtotal = 1000): OrderStore
    {
        $customer = User::factory()->customer()->create();
        $order = Order::query()->create([
            'order_number' => 'TEST-'.uniqid(),
            'customer_id' => $customer->id,
            'total_amount' => $subtotal,
            'delivery_address' => 'x',
            'status' => $status === 'completed' ? 'completed' : 'processing',
        ]);

        return OrderStore::query()->create([
            'order_id' => $order->id,
            'store_id' => $this->store->id,
            'subtotal' => $subtotal,
            'status' => $status,
            'customer_confirmed_at' => $status === 'completed' ? now() : null,
        ]);
    }

    public function test_eligible_lists_only_completed_order_stores_without_payout(): void
    {
        $eligible = $this->orderStore('completed', 1000);
        $this->orderStore('delivered'); // العميل لم يؤكد الاستلام بعد
        $paid = $this->orderStore('completed');
        StorePayout::query()->create(['order_store_id' => $paid->id, 'total_amount' => 1000, 'commission_rate' => 5, 'commission_amount' => 50, 'net_amount' => 950, 'status' => 'pending']);

        $res = $this->getJson('/api/admin/finance/payouts/eligible')->assertOk();

        $this->assertSame([$eligible->id], array_column($res->json('data'), 'order_store_id'));
        $res->assertJsonPath('data.0.subtotal', 1000)
            ->assertJsonPath('data.0.wallets.0.id', $this->wallet->id)
            ->assertJsonPath('data.0.wallets.0.account_last4', '3456')
            ->assertJsonPath('data.0.wallets.0.is_default', true);
        $this->assertStringNotContainsString('777123456', $res->getContent());
    }

    public function test_admin_creates_payout_with_server_computed_amounts(): void
    {
        $os = $this->orderStore('completed', 1000);

        $this->postJson('/api/store_payouts', ['order_store_id' => $os->id, 'commission_rate' => 5, 'user_wallet_id' => $this->wallet->id])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('store_payouts', ['order_store_id' => $os->id, 'commission_amount' => 50, 'net_amount' => 950]);
        $this->assertSame([], $this->getJson('/api/admin/finance/payouts/eligible')->json('data'));
    }

    public function test_cannot_create_payout_before_customer_confirms(): void
    {
        $os = $this->orderStore('delivered');

        $this->postJson('/api/store_payouts', ['order_store_id' => $os->id, 'commission_rate' => 5])->assertStatus(422);
    }

    public function test_duplicate_payout_is_rejected(): void
    {
        $os = $this->orderStore('completed');
        $this->postJson('/api/store_payouts', ['order_store_id' => $os->id, 'commission_rate' => 5])->assertCreated();

        $this->postJson('/api/store_payouts', ['order_store_id' => $os->id, 'commission_rate' => 5])->assertStatus(409);
        $this->assertSame(1, StorePayout::count());
    }

    public function test_failed_payout_can_be_retried(): void
    {
        $os = $this->orderStore('completed');
        $id = $this->postJson('/api/store_payouts', ['order_store_id' => $os->id, 'commission_rate' => 5])->json('data.id');
        $this->putJson("/api/store_payouts/{$id}", ['status' => 'failed'])->assertOk();

        $this->assertSame([$os->id], array_column($this->getJson('/api/admin/finance/payouts/eligible')->json('data'), 'order_store_id'));

        $this->postJson('/api/store_payouts', ['order_store_id' => $os->id, 'commission_rate' => 3])->assertCreated();
        $this->assertDatabaseHas('store_payouts', ['id' => $id, 'status' => 'pending', 'commission_rate' => 3]);
        $this->assertSame(1, StorePayout::count());
    }

    public function test_wallet_of_another_user_is_rejected(): void
    {
        $os = $this->orderStore('completed');
        $other = UserWallet::query()->create(['user_id' => User::factory()->create()->id, 'wallet_provider_id' => $this->wallet->wallet_provider_id, 'account_number' => '1', 'account_name' => 'x', 'is_default' => true, 'is_active' => true]);

        $this->postJson('/api/store_payouts', ['order_store_id' => $os->id, 'commission_rate' => 5, 'user_wallet_id' => $other->id])->assertStatus(422);
    }

    public function test_supplier_cannot_list_eligible_or_create(): void
    {
        Sanctum::actingAs($this->supplier);
        $os = $this->orderStore('completed');

        $this->getJson('/api/admin/finance/payouts/eligible')->assertForbidden();
        $this->postJson('/api/store_payouts', ['order_store_id' => $os->id, 'commission_rate' => 0])->assertForbidden();
    }
}
