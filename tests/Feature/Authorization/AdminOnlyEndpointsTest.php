<?php

namespace Tests\Feature\Authorization;

use App\Models\Specialization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * مسارات كانت مفتوحة لأي مستخدم مسجّل (أو بدون تسجيل عبر Nuxt) وأصبحت للمدير فقط.
 */
class AdminOnlyEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public static function adminOnlyGetEndpoints(): array
    {
        return [
            'dashboard stats' => ['/api/admin/dashboard/stats'],
            'all service requests' => ['/api/service-requests'],
            'all orders' => ['/api/admin/orders'],
            'all payments' => ['/api/admin/finance/payments'],
            'all payouts' => ['/api/admin/finance/payouts'],
            'finance stats' => ['/api/admin/finance/stats'],
            'wallet providers' => ['/api/admin/wallet-providers'],
        ];
    }

    #[DataProvider('adminOnlyGetEndpoints')]
    public function test_non_admin_roles_are_forbidden(string $uri): void
    {
        foreach (['customer', 'supplier', 'engineer'] as $role) {
            $user = User::factory()->create();
            $user->syncRoles([$role]);
            Sanctum::actingAs($user);

            $this->getJson($uri)->assertForbidden();
        }
    }

    #[DataProvider('adminOnlyGetEndpoints')]
    public function test_guests_get_json_401_even_without_accept_header(string $uri): void
    {
        $this->get($uri)->assertUnauthorized()->assertJson(['message' => 'Unauthenticated.']);
    }

    #[DataProvider('adminOnlyGetEndpoints')]
    public function test_admin_can_access(string $uri): void
    {
        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);
        Sanctum::actingAs($admin);

        $this->getJson($uri)->assertOk();
    }

    public function test_customer_cannot_manage_specializations_or_service_types(): void
    {
        $customer = User::factory()->create();
        $customer->syncRoles(['customer']);
        Sanctum::actingAs($customer);

        $this->postJson('/api/specializations', ['name' => 'Hacked'])->assertForbidden();
        $this->postJson('/api/service-types', ['name_ar' => 'Hacked'])->assertForbidden();

        $this->assertSame(0, Specialization::where('name', 'Hacked')->count());
    }

    public function test_admin_can_create_specialization(): void
    {
        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);
        Sanctum::actingAs($admin);

        $this->postJson('/api/specializations', ['name' => 'Solar Design'])->assertCreated();
    }

    public function test_dashboard_stats_returns_expected_keys(): void
    {
        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/dashboard/stats')
            ->assertOk()
            ->assertJsonStructure(['status', 'data' => [
                'users_count', 'approved_stores_count', 'pending_stores_count',
                'approved_engineers_count', 'pending_engineers_count',
                'orders_count', 'pending_orders_count', 'open_services_count',
            ]]);
    }
}
