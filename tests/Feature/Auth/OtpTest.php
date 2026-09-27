<?php

namespace Tests\Feature\Auth;

use App\Mail\VerificationOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'verification.otp.length' => 6,
            'verification.otp.expires_minutes' => 10,
            'verification.otp.max_attempts' => 5,
            'verification.otp.resend_max_attempts' => 3,
            'verification.otp.resend_decay_seconds' => 60,
        ]);
    }

    public function test_registration_creates_and_sends_otp(): void
    {
        Mail::fake();

        Role::findOrCreate('customer', 'web');

        $response = $this->postJson('/api/auth/register', [
            'name' => 'OTP Test User',
            'email' => 'otp@example.com',
            'phone' => '+201001234567',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'customer',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('status', 'success');

        $user = User::query()
            ->where('email', 'otp@example.com')
            ->firstOrFail();

        $this->assertNull($user->email_verified_at);

        $this->assertDatabaseHas('email_otps', [
            'user_id' => $user->id,
            'attempts' => 0,
            'invalidated_at' => null,
        ]);

        Mail::assertSent(
            VerificationOtpMail::class,
            function (VerificationOtpMail $mail): bool {
                return preg_match(
                    '/^[0-9]{6}$/',
                    $mail->code
                ) === 1
                    && $mail->expiresInMinutes === 10;
            }
        );
    }

    public function test_user_can_verify_email_with_valid_otp(): void
    {
        $user = User::factory()
            ->unverified()
            ->create();

        $otp = EmailOtp::query()->create([
            'user_id' => $user->id,
            'code' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'invalidated_at' => null,
        ]);

        $response = $this->postJson('/api/auth/otp/verify', [
            'email' => $user->email,
            'code' => '123456',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertNotNull(
            $user->fresh()->email_verified_at
        );

        $this->assertNotNull(
            $otp->fresh()->invalidated_at
        );
    }

    public function test_wrong_otp_increases_attempts(): void
    {
        $user = User::factory()
            ->unverified()
            ->create();

        $otp = EmailOtp::query()->create([
            'user_id' => $user->id,
            'code' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'invalidated_at' => null,
        ]);

        $response = $this->postJson('/api/auth/otp/verify', [
            'email' => $user->email,
            'code' => '654321',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');

        $this->assertSame(
            1,
            $otp->fresh()->attempts
        );

        $this->assertNull(
            $user->fresh()->email_verified_at
        );
    }

    public function test_expired_otp_cannot_verify_email(): void
    {
        $user = User::factory()
            ->unverified()
            ->create();

        $otp = EmailOtp::query()->create([
            'user_id' => $user->id,
            'code' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->subMinute(),
            'invalidated_at' => null,
        ]);

        $response = $this->postJson('/api/auth/otp/verify', [
            'email' => $user->email,
            'code' => '123456',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');

        $this->assertNull(
            $user->fresh()->email_verified_at
        );

        $this->assertNotNull(
            $otp->fresh()->invalidated_at
        );
    }

    public function test_resend_invalidates_old_otp_and_sends_new_one(): void
    {
        Mail::fake();

        $user = User::factory()
            ->unverified()
            ->create();

        $oldOtp = EmailOtp::query()->create([
            'user_id' => $user->id,
            'code' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'invalidated_at' => null,
        ]);

        $response = $this->postJson('/api/auth/otp/resend', [
            'email' => $user->email,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertNotNull(
            $oldOtp->fresh()->invalidated_at
        );

        $this->assertDatabaseCount('email_otps', 2);

        Mail::assertSent(
            VerificationOtpMail::class,
            fn (VerificationOtpMail $mail): bool =>
                preg_match('/^[0-9]{6}$/', $mail->code) === 1
        );
    }

    public function test_resend_does_not_reveal_unknown_email(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/otp/resend', [
            'email' => 'unknown@example.com',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath(
                'message',
                'If the account exists and is not verified, a new code has been sent.'
            );

        Mail::assertNothingSent();
    }
}
