<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MasterProduct;
use App\Models\Order;
use App\Models\QuoteRequest;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * قبول عرض السعر يُنشئ طلب شراء بالسعر المتفق عليه (وليس سعر القائمة)، ثم يمر الطلب
 * بالمسار المعتاد: دفع مسبق ← اعتماد ← شحن.
 */
class QuoteToOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $supplier;
    private StoreProduct $product;
    private QuoteRequest $quote;

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
        $category = Category::query()->create(['name_ar' => 'ألواح', 'name_en' => 'Panels', 'slug' => 'panels']);
        $brand = Brand::query()->create(['name' => 'Test Brand']);
        $master = MasterProduct::query()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'title' => 'Test Panel']);
        $this->product = StoreProduct::query()->create([
            'store_id' => $store->id,
            'master_product_id' => $master->id,
            'price' => 100,
            'stock_quantity' => 10,
            'min_order_qty' => 1,
            'status' => 'active',
        ]);
        $this->quote = QuoteRequest::query()->create([
            'customer_id' => $this->customer->id,
            'store_product_id' => $this->product->id,
            'quantity' => 4,
            'offered_unit_price' => 80,
            'total_price' => 320,
            'status' => 'responded',
        ]);
    }

    private function accept(array $body = ['delivery_address' => 'صنعاء - شارع الزبيري'])
    {
        Sanctum::actingAs($this->customer);

        return $this->patchJson("/api/quote-requests/{$this->quote->id}/accept", $body);
    }

    public function test_accepting_a_quote_creates_an_order_at_the_quoted_price(): void
    {
        $res = $this->accept()->assertOk();

        $quote = $this->quote->fresh();
        $this->assertSame('accepted', $quote->status);
        $this->assertNotNull($quote->order_id);
        $res->assertJsonPath('data.order_id', $quote->order_id);

        $order = Order::with('orderStores.items')->find($quote->order_id);
        $this->assertSame($this->customer->id, $order->customer_id);
        $this->assertSame('pending', $order->status);
        $this->assertEquals(320, (float) $order->total_amount); // 4 × 80، وليس 4 × 100
        $item = $order->orderStores->first()->items->first();
        $this->assertEquals(80, (float) $item->unit_price);
        $this->assertSame(4, (int) $item->quantity);

        $this->assertSame(6, $this->product->fresh()->stock_quantity);
    }

    public function test_the_order_appears_in_the_customer_orders_unpaid(): void
    {
        $this->accept()->assertOk();

        $this->getJson('/api/my/orders')
            ->assertOk()
            ->assertJsonPath('data.0.payment.status', 'unpaid')
            ->assertJsonPath('data.0.payment.remaining', 320);
    }

    public function test_delivery_address_is_required(): void
    {
        $this->accept([])->assertStatus(422)->assertJsonValidationErrors('delivery_address');

        $this->assertSame('responded', $this->quote->fresh()->status);
        $this->assertSame(0, Order::count());
    }

    public function test_a_quote_cannot_create_two_orders(): void
    {
        $this->accept()->assertOk();
        $this->accept()->assertStatus(422);

        $this->assertSame(1, Order::count());
        $this->assertSame(6, $this->product->fresh()->stock_quantity);
    }

    public function test_pending_quote_without_store_price_cannot_be_accepted(): void
    {
        $this->quote->update(['status' => 'pending', 'offered_unit_price' => null, 'total_price' => null]);

        $this->accept()->assertStatus(422);
        $this->assertSame(0, Order::count());
    }

    public function test_insufficient_stock_is_rejected_without_side_effects(): void
    {
        $this->product->update(['stock_quantity' => 2]);

        $this->accept()->assertStatus(422);

        $this->assertSame('responded', $this->quote->fresh()->status);
        $this->assertNull($this->quote->fresh()->order_id);
        $this->assertSame(0, Order::count());
        $this->assertSame(2, $this->product->fresh()->stock_quantity);
    }

    public function test_legacy_accepted_quote_without_order_can_be_converted(): void
    {
        $this->quote->update(['status' => 'accepted', 'accepted_at' => now()->subDay()]);

        $this->accept()->assertOk();

        $this->assertNotNull($this->quote->fresh()->order_id);
        $this->assertSame(1, Order::count());
    }

    public function test_another_customer_cannot_accept(): void
    {
        Sanctum::actingAs(User::factory()->customer()->create());

        $this->patchJson("/api/quote-requests/{$this->quote->id}/accept", ['delivery_address' => 'x'])
            ->assertForbidden();
        $this->assertSame(0, Order::count());
    }

    public function test_accept_notifies_supplier_once_with_the_new_order(): void
    {
        $this->seed(\Database\Seeders\NotificationTemplatesSeeder::class);

        $this->accept()->assertOk();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->supplier->id, 'template_code' => 'order_created_store']);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->customer->id, 'template_code' => 'order_created_customer']);
        $this->assertDatabaseMissing('notifications', ['template_code' => 'quote_accepted_store']);
    }

    public function test_quote_order_cannot_ship_before_payment(): void
    {
        $this->accept()->assertOk();
        $orderStore = Order::find($this->quote->fresh()->order_id)->orderStores()->first();

        Sanctum::actingAs($this->supplier);
        $this->patchJson("/api/order-stores/{$orderStore->id}/status", ['status' => 'accepted'])->assertOk();
        $this->patchJson("/api/order-stores/{$orderStore->id}/status", ['status' => 'shipped'])->assertStatus(422);
    }
}
