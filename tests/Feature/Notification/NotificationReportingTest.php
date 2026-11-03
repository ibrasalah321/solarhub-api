<?php

namespace Tests\Feature\Notification;

use App\Models\Notification;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Database\Seeders\NotificationTemplatesSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationReportingTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NotificationTemplatesSeeder::class);
        $this->service = app(NotificationService::class);
    }

    public function test_registration_notifies_only_after_successful_creation(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Mail::fake();

        $invalid = [
            'name' => 'Invalid',
            'email' => 'invalid@example.com',
            'phone' => '+201000009001',
            'password' => 'Password123',
            'password_confirmation' => 'different',
        ];

        $this->postJson('/api/auth/customer/register', $invalid)
            ->assertUnprocessable();
        $this->assertDatabaseCount('notifications', 0);

        $valid = $invalid;
        $valid['email'] = 'valid@example.com';
        $valid['phone'] = '+201000009002';
        $valid['password_confirmation'] = 'Password123';

        $this->postJson('/api/auth/customer/register', $valid)
            ->assertCreated();

        $user = User::query()->where('email', 'valid@example.com')->firstOrFail();
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'template_code' => 'account_created',
            'sender_id' => null,
            'sender_type' => 'system',
        ]);
    }

    public function test_template_rendering_recipients_and_retry_deduplication(): void
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(2)->create();
        $created = $this->service->sendToUsers(
            $recipients, 'order_created_store',
            ['store_name' => 'متجر الشمس', 'order_number' => 'ORD-1', 'amount' => '1200'],
            'order:1:created', $sender
        );
        $this->assertCount(2, $created);
        $this->service->sendToUsers(
            $recipients, 'order_created_store',
            ['store_name' => 'متجر الشمس', 'order_number' => 'ORD-1', 'amount' => '1200'],
            'order:1:created', $sender
        );
        $this->assertDatabaseCount('notifications', 2);
        $this->assertStringContainsString('ORD-1', $created->first()->data['body']);
        $this->assertEqualsCanonicalizing($recipients->pluck('id')->all(), Notification::pluck('notifiable_id')->all());
    }

    public function test_inbox_outbox_grouping_and_independent_read_state(): void
    {
        $sender = User::factory()->create();
        [$first,$second] = User::factory()->count(2)->create()->all();
        $rows = $this->service->sendToUsers(
            [$first, $second], 'order_created_store',
            ['store_name' => 'متجر', 'order_number' => 'ORD-2', 'amount' => '50'],
            'order:2:created', $sender
        );
        $this->service->markAsRead($first, $rows->first());
        $this->assertNotNull($rows->first()->fresh()->read_at);
        $this->assertNull($rows->last()->fresh()->read_at);
        $outbox = $this->service->outbox($sender);
        $this->assertCount(1, $outbox->items());
        $this->assertSame(2, (int) $outbox->first()->recipient_count);
        $this->assertCount(1, $this->service->inbox($first)->items());
        $this->assertCount(1, $this->service->inbox($second)->items());
    }

    public function test_self_recipient_is_outgoing_not_incoming(): void
    {
        $user = User::factory()->create();
        $this->service->sendToUsers(
            [$user], 'account_created',
            ['user_name' => $user->name, 'account_type' => 'عميل'],
            'self-event', $user
        );
        $this->assertCount(0, $this->service->inbox($user)->items());
        $this->assertCount(1, $this->service->outbox($user)->items());
    }

    public function test_system_and_legacy_unknown_senders_are_distinct(): void
    {
        $user = User::factory()->create();
        $this->service->sendToUsers(
            [$user], 'account_created',
            ['user_name' => $user->name, 'account_type' => 'عميل'],
            'system-event', systemGenerated: true
        );
        Notification::query()->create([
            'type' => 'legacy', 'notifiable_type' => User::class,
            'notifiable_id' => $user->id, 'data' => ['title' => 'قديم', 'body' => 'قديم'],
        ]);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/my/notifications/inbox')->assertOk();
        $types = collect($response->json('data'))->pluck('sender.type');
        $this->assertTrue($types->contains('system'));
        $this->assertTrue($types->contains('legacy_unknown'));
    }

    public function test_user_cannot_read_another_users_notification_or_set_sender_from_body(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $notification = $this->service->sendToUsers(
            [$owner], 'account_created',
            ['user_name' => $owner->name, 'account_type' => 'عميل'],
            'private-event', systemGenerated: true
        )->first();
        Sanctum::actingAs($attacker);
        $this->patchJson("/api/my/notifications/{$notification->id}/read", [
            'sender_id' => $attacker->id,
        ])->assertForbidden();
        $this->assertNull($notification->fresh()->read_at);
        $this->assertNull($notification->fresh()->sender_id);
    }
}
