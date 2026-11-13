<?php

namespace Tests\Feature\Auth;

use App\Models\EmailOtp;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * التسجيل يجلب الحسابات المطابقة للبريد والجوال باستعلامين ثم يقرر في الذاكرة.
 * هذه الاختبارات تثبّت كل قرار من تلك القرارات.
 */
class RegistrationDuplicateHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Mail::fake();
    }

    private function register(string $email = 'new@example.com', string $phone = '+967770000001', string $role = 'customer')
    {
        return $this->postJson("/api/auth/register", [
            'role' => $role,
            'name' => 'Dup Test',
            'email' => $email,
            'phone' => $phone,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);
    }

    private function user(array $attrs): User
    {
        // email_verified_at غير قابل للإسناد الجماعي، لذلك forceFill
        $user = (new User)->forceFill($attrs + [
            'name' => 'Existing',
            'password' => Hash::make('Old-Password1'),
            'status' => 'active',
        ]);
        $user->save();

        return $user;
    }

    public function test_new_customer_registration_creates_one_active_otp(): void
    {
        $this->register()->assertCreated()->assertJsonPath('data.role', 'customer');

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertNull($user->store);
        $this->assertNull($user->engineerProfile);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertSame(1, EmailOtp::where('user_id', $user->id)->whereNull('invalidated_at')->count());
    }

    public function test_verified_email_is_rejected(): void
    {
        $this->user(['email' => 'new@example.com', 'phone' => '+967779999999', 'email_verified_at' => now()]);

        $this->register()->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_verified_phone_is_rejected(): void
    {
        $this->user(['email' => 'other@example.com', 'phone' => '+967770000001', 'email_verified_at' => now()]);

        $this->register()->assertStatus(422)->assertJsonValidationErrors('phone');
    }

    public function test_email_of_a_soft_deleted_verified_account_gets_a_clear_error(): void
    {
        $this->user(['email' => 'new@example.com', 'phone' => '+967779999999', 'email_verified_at' => now()])->delete();

        $this->register()->assertStatus(422)->assertJsonValidationErrors('email');
        $this->assertStringContainsString('محذوف', $this->register()->json('errors.email.0'));
    }

    public function test_unverified_account_with_same_email_is_reused_and_old_otps_invalidated(): void
    {
        $existing = $this->user(['email' => 'new@example.com', 'phone' => '+967779999999']);
        $existing->assignRole('customer');
        $old = EmailOtp::query()->create(['user_id' => $existing->id, 'code' => Hash::make('111111'), 'attempts' => 0, 'expires_at' => now()->addMinutes(10)]);

        $this->register()->assertCreated();

        $this->assertSame(1, User::withTrashed()->where('email', 'new@example.com')->count());
        $fresh = $existing->fresh();
        $this->assertSame('+967770000001', $fresh->phone);
        $this->assertTrue(Hash::check('Password123', $fresh->password));
        $this->assertNotNull($old->fresh()->invalidated_at);
        $this->assertSame(1, EmailOtp::where('user_id', $existing->id)->whereNull('invalidated_at')->count());
    }

    public function test_soft_deleted_unverified_account_with_same_email_is_restored(): void
    {
        $existing = $this->user(['email' => 'new@example.com', 'phone' => '+967779999999']);
        $existing->delete();

        $this->register()->assertCreated();

        $this->assertFalse($existing->fresh()->trashed());
        $this->assertSame(1, User::withTrashed()->where('email', 'new@example.com')->count());
    }

    public function test_abandoned_unverified_account_holding_the_phone_is_removed(): void
    {
        $abandoned = $this->user(['email' => 'abandoned@example.com', 'phone' => '+967770000001']);

        $this->register()->assertCreated();

        $this->assertNull(User::withTrashed()->find($abandoned->id));
        $this->assertSame('+967770000001', User::where('email', 'new@example.com')->value('phone'));
    }

    public function test_phone_of_a_soft_deleted_verified_account_is_rejected_and_kept(): void
    {
        $old = $this->user(['email' => 'old@example.com', 'phone' => '+967770000001', 'email_verified_at' => now()]);
        $old->delete();

        $this->register()->assertStatus(422)->assertJsonValidationErrors('phone');
        $this->assertNotNull(User::withTrashed()->find($old->id));
    }

    public function test_unverified_account_with_data_holding_the_phone_is_rejected_and_kept(): void
    {
        $owner = $this->user(['email' => 'owner@example.com', 'phone' => '+967770000001']);
        Store::query()->create(['user_id' => $owner->id, 'company_name' => 'Kept', 'store_type' => 'retailer', 'address_details' => 'x', 'approval_status' => 'pending']);

        $this->register()->assertStatus(422)->assertJsonValidationErrors('phone');
        $this->assertNotNull(User::find($owner->id));
    }
}
