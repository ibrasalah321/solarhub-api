<?php

namespace Tests\Feature;

use App\Http\Resources\Cart\CartItemResource;
use App\Http\Resources\Order\OrderItemResource;
use App\Http\Resources\Order\QuoteRequestResource;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\QuoteRequest;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * إلغاء العميل للطلب كان يتم في القاعدة ثم يرجع 500: OrderItemResource كان يعامل MissingValue
 * (العلاقة غير محمّلة) كأنه null.
 */
class CustomerCancelOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cancel_returns_200(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $customer = User::factory()->customer()->create();
        $supplier = User::factory()->supplier()->create();
        $store = Store::query()->create(['user_id' => $supplier->id, 'company_name' => 'S', 'store_type' => 'retailer', 'address_details' => 'x', 'approval_status' => 'approved']);
        $order = Order::query()->create(['order_number' => 'T-'.uniqid(), 'customer_id' => $customer->id, 'total_amount' => 100, 'delivery_address' => 'x', 'status' => 'pending']);
        $os = $order->orderStores()->create(['store_id' => $store->id, 'subtotal' => 100, 'status' => 'pending']);
        $os->items()->create(['store_product_id' => $this->storeProductId($store), 'unit_price' => 100, 'quantity' => 1, 'total_price' => 100]);

        Sanctum::actingAs($customer);

        $this->patchJson("/api/my/orders/{$order->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_item_resources_without_loaded_store_product_do_not_crash(): void
    {
        $request = request();

        $this->assertSame('منتج طاقة شمسية', (new OrderItemResource(new OrderItem(['unit_price' => 1, 'quantity' => 1, 'total_price' => 1])))->toArray($request)['product_title']);
        $this->assertIsArray((new QuoteRequestResource(new QuoteRequest(['quantity' => 1, 'status' => 'pending'])))->toArray($request));
        $this->assertIsArray((new CartItemResource(new CartItem(['quantity' => 1])))->toArray($request));
    }

    private function storeProductId(Store $store): int
    {
        $category = \App\Models\Category::query()->create(['name_ar' => 'ألواح', 'name_en' => 'Panels', 'slug' => 'panels']);
        $brand = \App\Models\Brand::query()->create(['name' => 'B']);
        $master = \App\Models\MasterProduct::query()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'title' => 'P']);

        return \App\Models\StoreProduct::query()->create(['store_id' => $store->id, 'master_product_id' => $master->id, 'price' => 100, 'stock_quantity' => 5, 'min_order_qty' => 1, 'status' => 'active'])->id;
    }
}
