<?php

namespace Tests\Feature;

use App\Models\EngineerProfile;
use App\Models\EngineerRating;
use App\Models\Offer;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\NotificationTemplatesSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * دورة حياة طلب الخدمة بعد قبول العرض وتقييم المهندس:
 * awarded → start → in_progress → mark-done → confirm-completion → completed → engineer-ratings
 */
class ServiceRequestLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $otherCustomer;
    private EngineerProfile $engineer;
    private EngineerProfile $otherEngineer;
    private ServiceRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(NotificationTemplatesSeeder::class);

        $this->customer = User::factory()->create();
        $this->customer->syncRoles(['customer']);
        $this->otherCustomer = User::factory()->create();
        $this->otherCustomer->syncRoles(['customer']);

        // إنشاء مباشر (المصانع تستخدم دوال PostGIS غير المتاحة في SQLite)
        $this->engineer = $this->makeEngineer();
        $this->otherEngineer = $this->makeEngineer();

        $serviceType = ServiceType::query()->create(['name' => 'Installation']);
        $this->request = ServiceRequest::query()->create([
            'customer_id' => $this->customer->id,
            'service_type_id' => $serviceType->id,
            'description' => 'Lifecycle test request',
            'status' => 'awarded',
        ]);

        foreach ([[$this->engineer, 'accepted'], [$this->otherEngineer, 'rejected']] as [$engineer, $status]) {
            Offer::query()->create([
                'service_request_id' => $this->request->id,
                'engineer_id' => $engineer->id,
                'proposed_cost' => 5000,
                'execution_time_days' => 7,
                'technical_proposal' => 'Proposal',
                'status' => $status,
            ]);
        }
    }

    private function makeEngineer(): EngineerProfile
    {
        $user = User::factory()->create();
        $user->syncRoles(['engineer']);

        return EngineerProfile::query()->create([
            'user_id' => $user->id,
            'license_number' => 'LIC-'.$user->id,
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    private function asEngineer(EngineerProfile $engineer): void
    {
        Sanctum::actingAs($engineer->user);
    }

    private function url(string $action): string
    {
        return "/api/service-requests/{$this->request->id}/{$action}";
    }

    // ---------- start ----------

    public function test_awarded_engineer_starts_work_and_customer_is_notified(): void
    {
        $this->asEngineer($this->engineer);

        $this->postJson($this->url('start'))
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $this->request->refresh();
        $this->assertSame('in_progress', $this->request->status);
        $this->assertNotNull($this->request->started_at);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->customer->id,
            'template_code' => 'service_started_customer',
        ]);
    }

    public function test_engineer_whose_offer_was_not_accepted_cannot_start(): void
    {
        $this->asEngineer($this->otherEngineer);

        $this->postJson($this->url('start'))->assertForbidden();
        $this->assertSame('awarded', $this->request->fresh()->status);
    }

    public function test_customer_cannot_start(): void
    {
        Sanctum::actingAs($this->customer);

        $this->postJson($this->url('start'))->assertForbidden();
    }

    public function test_cannot_start_twice(): void
    {
        $this->asEngineer($this->engineer);
        $this->postJson($this->url('start'))->assertOk();

        $this->postJson($this->url('start'))->assertStatus(422);
    }

    public function test_cannot_start_a_request_that_is_still_open(): void
    {
        $this->request->update(['status' => 'open_for_bids']);
        $this->asEngineer($this->engineer);

        $this->postJson($this->url('start'))->assertStatus(422);
    }

    // ---------- mark-done ----------

    public function test_mark_done_requires_in_progress(): void
    {
        $this->asEngineer($this->engineer);

        $this->postJson($this->url('mark-done'))->assertStatus(422);
        $this->assertNull($this->request->fresh()->engineer_completed_at);
    }

    public function test_awarded_engineer_marks_done_once_and_customer_is_notified(): void
    {
        $this->asEngineer($this->engineer);
        $this->postJson($this->url('start'))->assertOk();

        $this->postJson($this->url('mark-done'))->assertOk()->assertJsonPath('data.status', 'in_progress');
        $this->assertNotNull($this->request->fresh()->engineer_completed_at);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->customer->id,
            'template_code' => 'service_marked_done_customer',
        ]);

        $this->postJson($this->url('mark-done'))->assertStatus(422);
    }

    public function test_other_engineer_cannot_mark_done(): void
    {
        $this->request->update(['status' => 'in_progress', 'started_at' => now()]);
        $this->asEngineer($this->otherEngineer);

        $this->postJson($this->url('mark-done'))->assertForbidden();
    }

    // ---------- confirm-completion ----------

    public function test_customer_cannot_confirm_before_engineer_marks_done(): void
    {
        $this->request->update(['status' => 'in_progress', 'started_at' => now()]);
        Sanctum::actingAs($this->customer);

        $this->postJson($this->url('confirm-completion'))->assertStatus(422);
        $this->assertSame('in_progress', $this->request->fresh()->status);
    }

    public function test_customer_confirms_completion_and_engineer_is_notified(): void
    {
        $this->request->update(['status' => 'in_progress', 'started_at' => now(), 'engineer_completed_at' => now()]);
        Sanctum::actingAs($this->customer);

        $this->postJson($this->url('confirm-completion'))
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->request->refresh();
        $this->assertSame('completed', $this->request->status);
        $this->assertNotNull($this->request->completed_at);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->engineer->user_id,
            'template_code' => 'service_completed_engineer',
        ]);
    }

    public function test_other_customer_cannot_confirm_completion(): void
    {
        $this->request->update(['status' => 'in_progress', 'engineer_completed_at' => now()]);
        Sanctum::actingAs($this->otherCustomer);

        $this->postJson($this->url('confirm-completion'))->assertForbidden();
    }

    public function test_engineer_cannot_confirm_completion(): void
    {
        $this->request->update(['status' => 'in_progress', 'engineer_completed_at' => now()]);
        $this->asEngineer($this->engineer);

        $this->postJson($this->url('confirm-completion'))->assertForbidden();
    }

    public function test_cannot_confirm_an_awarded_request_that_never_started(): void
    {
        Sanctum::actingAs($this->customer);

        $this->postJson($this->url('confirm-completion'))->assertStatus(422);
    }

    // ---------- engineer ratings ----------

    public function test_customer_rates_engineer_once_after_completion(): void
    {
        $this->request->update(['status' => 'completed', 'completed_at' => now()]);
        Sanctum::actingAs($this->customer);

        $this->postJson('/api/engineer-ratings', [
            'service_request_id' => $this->request->id,
            'rating' => 5,
            'comment' => 'عمل ممتاز',
        ])->assertCreated();

        $this->assertDatabaseHas('engineer_ratings', [
            'service_request_id' => $this->request->id,
            'engineer_id' => $this->engineer->id,
            'customer_id' => $this->customer->id,
            'rating' => 5,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->engineer->user_id,
            'template_code' => 'engineer_rated_engineer',
        ]);

        $this->postJson('/api/engineer-ratings', [
            'service_request_id' => $this->request->id,
            'rating' => 4,
        ])->assertStatus(422);

        $this->assertSame(1, EngineerRating::where('service_request_id', $this->request->id)->count());
    }

    public function test_cannot_rate_before_completion(): void
    {
        $this->request->update(['status' => 'in_progress']);
        Sanctum::actingAs($this->customer);

        $this->postJson('/api/engineer-ratings', [
            'service_request_id' => $this->request->id,
            'rating' => 5,
        ])->assertStatus(422);
    }

    public function test_other_customer_cannot_rate(): void
    {
        $this->request->update(['status' => 'completed']);
        Sanctum::actingAs($this->otherCustomer);

        $this->postJson('/api/engineer-ratings', [
            'service_request_id' => $this->request->id,
            'rating' => 5,
        ])->assertForbidden();
    }

    public function test_engineer_cannot_rate(): void
    {
        $this->request->update(['status' => 'completed']);
        $this->asEngineer($this->engineer);

        $this->postJson('/api/engineer-ratings', [
            'service_request_id' => $this->request->id,
            'rating' => 5,
        ])->assertForbidden();
    }

    public function test_rating_validation(): void
    {
        $this->request->update(['status' => 'completed']);
        Sanctum::actingAs($this->customer);

        $this->postJson('/api/engineer-ratings', ['service_request_id' => $this->request->id, 'rating' => 6])
            ->assertStatus(422)->assertJsonValidationErrors('rating');
        $this->postJson('/api/engineer-ratings', ['service_request_id' => 999999, 'rating' => 5])
            ->assertStatus(422)->assertJsonValidationErrors('service_request_id');
    }

    // ---------- visibility ----------

    public function test_awarded_engineer_can_view_the_request_but_others_cannot(): void
    {
        $this->asEngineer($this->engineer);
        $this->getJson("/api/service-requests/{$this->request->id}")->assertOk();

        $this->asEngineer($this->otherEngineer);
        $this->getJson("/api/service-requests/{$this->request->id}")->assertForbidden();
    }

    public function test_full_flow_end_to_end(): void
    {
        $this->asEngineer($this->engineer);
        $this->postJson($this->url('start'))->assertOk();
        $this->postJson($this->url('mark-done'))->assertOk();

        Sanctum::actingAs($this->customer);
        $this->postJson($this->url('confirm-completion'))->assertOk();
        $this->postJson('/api/engineer-ratings', ['service_request_id' => $this->request->id, 'rating' => 4])->assertCreated();

        $this->getJson("/api/service-requests/{$this->request->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.rating.rating', 4);
    }
}
