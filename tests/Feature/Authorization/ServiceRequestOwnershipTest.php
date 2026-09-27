<?php

namespace Tests\Feature\Authorization;

use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceRequestOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_cannot_update_or_cancel_another_customers_request(): void
    {
        $serviceRequest = $this->createServiceRequest();
        $otherCustomer = User::factory()->customer()->create();

        Sanctum::actingAs($otherCustomer);

        $this->putJson("/api/service-requests/{$serviceRequest->id}", [
            'description' => 'Changed by another customer',
        ])->assertForbidden();

        $this->patchJson("/api/service-requests/{$serviceRequest->id}/cancel")
            ->assertForbidden();

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'description' => 'Original description',
            'status' => 'open_for_bids',
        ]);
    }

    public function test_owner_can_update_open_request(): void
    {
        $customer = User::factory()->customer()->create();
        $serviceRequest = $this->createServiceRequest($customer);

        Sanctum::actingAs($customer);

        $this->putJson("/api/service-requests/{$serviceRequest->id}", [
            'description' => 'Updated description',
        ])->assertOk();

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'description' => 'Updated description',
        ]);
    }

    public function test_owner_can_cancel_open_request(): void
    {
        $customer = User::factory()->customer()->create();
        $serviceRequest = $this->createServiceRequest($customer);

        Sanctum::actingAs($customer);

        $this->patchJson("/api/service-requests/{$serviceRequest->id}/cancel")
            ->assertOk();

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => 'cancelled',
        ]);
    }

    private function createServiceRequest(?User $customer = null): ServiceRequest
    {
        $customer ??= User::factory()->customer()->create();

        $serviceType = ServiceType::query()->create([
            'name' => 'Installation',
        ]);

        return ServiceRequest::query()->create([
            'customer_id' => $customer->id,
            'service_type_id' => $serviceType->id,
            'description' => 'Original description',
            'status' => 'open_for_bids',
        ]);
    }
}
