<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use App\Services\Notification\NotificationService;
use Database\Seeders\NotificationTemplatesSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * NOTIFY_ONLY_TEST_ACCOUNTS: في التطوير لا تُنشأ إشعارات إلا للحسابات @solarhub.test.
 */
class NotifyOnlyTestAccountsTest extends TestCase
{
    use RefreshDatabase;

    private User $real;
    private User $test;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, NotificationTemplatesSeeder::class]);
        $this->real = User::factory()->create(['email' => 'real.person@example.com']);
        $this->test = User::factory()->create(['email' => 'customer@solarhub.test']);
    }

    private function send(): void
    {
        app(NotificationService::class)->sendToUsers(
            [$this->real, $this->test],
            'account_created',
            ['user_name' => 'X', 'account_type' => 'عميل'],
            'flag-test',
            systemGenerated: true
        );
    }

    public function test_flag_on_notifies_only_test_accounts(): void
    {
        config(['notifications.only_test_accounts' => true]);

        $this->send();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->test->id, 'template_code' => 'account_created']);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $this->real->id]);
    }

    public function test_flag_off_notifies_everyone(): void
    {
        config(['notifications.only_test_accounts' => false]);

        $this->send();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->real->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->test->id]);
    }

    public function test_flag_is_ignored_in_production(): void
    {
        config(['notifications.only_test_accounts' => true]);
        $this->app['env'] = 'production';

        try {
            $this->send();
        } finally {
            $this->app['env'] = 'testing';
        }

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->real->id]);
    }

    public function test_after_commit_domain_notifications_are_filtered_too(): void
    {
        config(['notifications.only_test_accounts' => true]);
        $realAdmin = User::factory()->create(['email' => 'boss@example.com']);
        $realAdmin->syncRoles(['admin']);
        $testAdmin = User::factory()->create(['email' => 'admin@solarhub.test']);
        $testAdmin->syncRoles(['admin']);
        $supplier = User::factory()->supplier()->create(['email' => 'shop@example.com']);

        app(\App\Services\Notification\DomainNotificationDispatcher::class)->accountCreated($supplier, 'supplier');

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $testAdmin->id, 'template_code' => 'professional_application_submitted_admin']);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $realAdmin->id]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $supplier->id]);
    }

    public function test_broadcast_reaches_only_test_accounts(): void
    {
        config(['notifications.only_test_accounts' => true]);
        $admin = User::factory()->create(['email' => 'admin@solarhub.test']);
        $admin->syncRoles(['admin']);
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/notifications/broadcast', ['title' => 'Test', 'content' => 'Broadcast flag test', 'audience' => 'all'])
            ->assertOk()
            ->assertJsonPath('data.recipients_count', 2); // customer@solarhub.test + admin@solarhub.test

        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $this->real->id, 'type' => 'broadcast']);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->test->id, 'type' => 'broadcast']);
    }
}
