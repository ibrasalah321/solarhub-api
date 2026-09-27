<?php

namespace Tests\Feature\Authorization;

use App\Models\EngineerProfile;
use App\Models\Offer;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EngineerOfferAuthorizationTest extends TestCase
{
    use RefreshDatabase;


    public function test_other_customer_cannot_accept_offer(): void
{
    $request = $this->createServiceRequest();
    [, $profile] = $this->createEngineer('approved');

    $offer = Offer::query()->create([
        'service_request_id' => $request->id,
        'engineer_id' => $profile->id,
        'proposed_cost' => 100,
        'execution_time_days' => 3,
        'technical_proposal' => 'Installation plan',
        'status' => 'pending',
    ]);

    Sanctum::actingAs(User::factory()->customer()->create());

    $this->patchJson("/api/offers/{$offer->id}/accept")
        ->assertForbidden();

    $this->assertDatabaseHas('offers', [
        'id' => $offer->id,
        'status' => 'pending',
    ]);
}

public function test_request_owner_can_accept_offer(): void
{
    $request = $this->createServiceRequest();
    [, $profile] = $this->createEngineer('approved');

    $offer = Offer::query()->create([
        'service_request_id' => $request->id,
        'engineer_id' => $profile->id,
        'proposed_cost' => 100,
        'execution_time_days' => 3,
        'technical_proposal' => 'Installation plan',
        'status' => 'pending',
    ]);

    Sanctum::actingAs($request->customer);

    $this->patchJson("/api/offers/{$offer->id}/accept")
        ->assertOk();

    $this->assertDatabaseHas('offers', [
        'id' => $offer->id,
        'status' => 'accepted',
    ]);

    $this->assertDatabaseHas('service_requests', [
        'id' => $request->id,
        'status' => 'awarded',
    ]);
}

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_cannot_submit_engineer_offer(): void
    {
        $request = $this->createServiceRequest();
        $customer = User::factory()->customer()->create();

        Sanctum::actingAs($customer);

        $this->postJson(
            "/api/service-requests/{$request->id}/offers",
            $this->offerData()
        )->assertForbidden();
    }

    public function test_pending_and_rejected_engineers_cannot_submit_offer(): void
    {
        $request = $this->createServiceRequest();

        foreach (['pending', 'rejected'] as $status) {
            [$engineer] = $this->createEngineer($status);
            Sanctum::actingAs($engineer);

            $this->postJson(
                "/api/service-requests/{$request->id}/offers",
                $this->offerData()
            )->assertForbidden();
        }

        $this->assertDatabaseCount('offers', 0);
    }

    public function test_approved_engineer_can_submit_offer(): void
    {
        $request = $this->createServiceRequest();
        [$engineer, $profile] = $this->createEngineer('approved');

        Sanctum::actingAs($engineer);

        $this->postJson(
            "/api/service-requests/{$request->id}/offers",
            $this->offerData()
        )->assertCreated();

        $this->assertDatabaseHas('offers', [
            'service_request_id' => $request->id,
            'engineer_id' => $profile->id,
            'status' => 'pending',
        ]);
    }

    public function test_engineer_cannot_update_another_engineers_offer(): void
    {
        $request = $this->createServiceRequest();
        [, $ownerProfile] = $this->createEngineer('approved');

        $offer = Offer::query()->create([
            'service_request_id' => $request->id,
            'engineer_id' => $ownerProfile->id,
            'proposed_cost' => 100,
            'execution_time_days' => 3,
            'technical_proposal' => 'Original proposal',
            'status' => 'pending',
        ]);

        [$otherEngineer] = $this->createEngineer('approved');
        Sanctum::actingAs($otherEngineer);

        $this->putJson("/api/offers/{$offer->id}", [
            'proposed_cost' => 200,
        ])->assertForbidden();

        $this->assertDatabaseHas('offers', [
            'id' => $offer->id,
            'proposed_cost' => 100,
        ]);
    }

    private function createServiceRequest(): ServiceRequest
    {
        $customer = User::factory()->customer()->create();
        $serviceType = ServiceType::query()->create([
            'name' => 'Installation',
        ]);

        return ServiceRequest::query()->create([
            'customer_id' => $customer->id,
            'service_type_id' => $serviceType->id,
            'description' => 'Install a solar system',
            'status' => 'open_for_bids',
        ]);
    }

    private function createEngineer(string $approvalStatus): array
    {
        $engineer = User::factory()->engineer()->create();

        $profile = EngineerProfile::query()->create([
            'user_id' => $engineer->id,
            'approval_status' => $approvalStatus,
        ]);

        return [$engineer, $profile];
    }

    private function offerData(): array
    {
        return [
            'proposed_cost' => 100,
            'execution_time_days' => 3,
            'technical_proposal' => 'Installation plan',
        ];
    }
}
