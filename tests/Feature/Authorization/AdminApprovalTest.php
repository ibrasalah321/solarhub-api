<?php

namespace Tests\Feature\Authorization;
use App\Models\EngineerProfile;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApprovalTest extends TestCase
{
    use RefreshDatabase;




    public function test_non_admin_roles_cannot_approve_or_reject_applications(): void
{
    $engineerUser = User::factory()->engineer()->create();
    $engineer = EngineerProfile::query()->create([
        'user_id' => $engineerUser->id,
        'approval_status' => 'pending',
    ]);

    $supplier = User::factory()->supplier()->create();
    $store = Store::query()->create([
        'user_id' => $supplier->id,
        'company_name' => 'Test Store',
        'store_type' => 'retailer',
        'address_details' => 'Test address',
        'approval_status' => 'pending',
    ]);

    foreach (['customer', 'supplier', 'engineer'] as $role) {
        $user = User::factory()->create();
        $user->syncRoles([$role]);
        Sanctum::actingAs($user);

        $this->patchJson("/api/admin/engineers/{$engineer->id}/approve")
            ->assertForbidden();

        $this->patchJson("/api/admin/stores/{$store->id}/reject", [
            'rejection_reason' => 'Incomplete documents',
        ])->assertForbidden();
    }

    $this->assertDatabaseHas('engineer_profile', [
        'id' => $engineer->id,
        'approval_status' => 'pending',
    ]);

    $this->assertDatabaseHas('stores', [
        'id' => $store->id,
        'approval_status' => 'pending',
    ]);
}

public function test_admin_can_approve_engineer_and_reject_store(): void
{
    $engineerUser = User::factory()->engineer()->create();
    $engineer = EngineerProfile::query()->create([
        'user_id' => $engineerUser->id,
        'approval_status' => 'pending',
    ]);

    $supplier = User::factory()->supplier()->create();
    $store = Store::query()->create([
        'user_id' => $supplier->id,
        'company_name' => 'Test Store',
        'store_type' => 'retailer',
        'address_details' => 'Test address',
        'approval_status' => 'pending',
    ]);

    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);
    Sanctum::actingAs($admin);

    $this->patchJson("/api/admin/engineers/{$engineer->id}/approve")
        ->assertOk();

    $this->patchJson("/api/admin/stores/{$store->id}/reject", [
        'rejection_reason' => 'Incomplete documents',
    ])->assertOk();

    $this->assertDatabaseHas('engineer_profile', [
        'id' => $engineer->id,
        'approval_status' => 'approved',
    ]);

    $this->assertDatabaseHas('stores', [
        'id' => $store->id,
        'approval_status' => 'rejected',
    ]);
}

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_cannot_view_pending_engineers(): void
    {
        $this->getJson('/api/admin/engineers/pending')
            ->assertUnauthorized();
    }

    public function test_non_admin_roles_cannot_view_pending_applications(): void
    {
        foreach (['customer', 'supplier', 'engineer'] as $role) {
            $user = User::factory()->create();
            $user->syncRoles([$role]);

            Sanctum::actingAs($user);

            $this->getJson('/api/admin/engineers/pending')
                ->assertForbidden();

            $this->getJson('/api/admin/stores/pending')
                ->assertForbidden();
        }
    }

    public function test_admin_can_view_pending_applications(): void
    {
        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/engineers/pending')
            ->assertOk();

        $this->getJson('/api/admin/stores/pending')
            ->assertOk();
    }
}
