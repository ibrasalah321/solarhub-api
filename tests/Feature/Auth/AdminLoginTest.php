<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_admin_is_verified_and_can_log_in(): void
    {
        putenv('SOLARHUB_ADMIN_PASSWORD=TestAdminPassword!123');
        $_ENV['SOLARHUB_ADMIN_PASSWORD'] = 'TestAdminPassword!123';
        $_SERVER['SOLARHUB_ADMIN_PASSWORD'] = 'TestAdminPassword!123';
        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'admin@solarhub.com')->firstOrFail();
        $this->assertNotNull($admin->email_verified_at);

        $this->postJson('/api/auth/login', [
            'login' => 'admin@solarhub.com',
            'password' => 'TestAdminPassword!123',
        ])->assertOk()->assertJsonStructure(['data' => ['token']]);

        putenv('SOLARHUB_ADMIN_PASSWORD');
        unset($_ENV['SOLARHUB_ADMIN_PASSWORD'], $_SERVER['SOLARHUB_ADMIN_PASSWORD']);
    }

    public function test_login_rate_limiter_returns_429_without_server_error(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/auth/login', [
                'login' => 'missing@example.test',
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/auth/login', [
            'login' => 'missing@example.test',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }
}
