<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_points_to_frontend_reset_page_with_token_and_email(): void
    {
        config(['app.frontend_url' => 'http://localhost:3000']);
        $user = User::factory()->create(['email' => 'reset-me@example.com']);

        $mail = (new ResetPassword('abc123token'))->toMail($user);

        $this->assertSame(
            'http://localhost:3000/auth/reset-password?token=abc123token&email=reset-me%40example.com',
            $mail->actionUrl
        );
    }

    public function test_reset_link_uses_the_configured_frontend_url(): void
    {
        config(['app.frontend_url' => 'https://solarhub.example/']);
        $user = User::factory()->create(['email' => 'a@example.com']);

        $url = (new ResetPassword('t'))->toMail($user)->actionUrl;

        $this->assertStringStartsWith('https://solarhub.example/auth/reset-password?', $url);
    }
}
