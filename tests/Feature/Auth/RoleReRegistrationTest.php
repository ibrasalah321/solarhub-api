<?php

namespace Tests\Feature\Auth;

use App\Mail\VerificationOtpMail;
use App\Models\EmailOtp;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * مسارات التسجيل التي تستخدمها الواجهة (/auth/{role}/register): من سجّل ولم يفعّل بريده يستطيع التسجيل
 * مجدداً، والحسابات المفعّلة فقط تمنع التسجيل.
 */
class RoleReRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Mail::fake();
        Storage::fake('supabase_private');
    }

    private function payload(array $extra = []): array
    {
        return $extra + [
            'name' => 'Re Register',
            'email' => 'again@example.com',
            'phone' => '+967770000002',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];
    }

    private function supplierFields(string $file = 'new.pdf'): array
    {
        return [
            'company_name' => 'Solar Supplier',
            'commercial_registry' => 'CR-100',
            'commercial_file' => UploadedFile::fake()->create($file, 100, 'application/pdf'),
            'address_details' => 'Sanaa',
            'store_type' => 'retailer',
        ];
    }

    private function unverified(string $role, array $attrs = []): User
    {
        $user = (new User)->forceFill($attrs + [
            'name' => 'First Try',
            'email' => 'again@example.com',
            'phone' => '+967770000001',
            'password' => Hash::make('Old-Password1'),
            'status' => 'active',
        ]);
        $user->save();
        $user->assignRole($role);

        return $user;
    }

    public function test_unverified_customer_can_register_again_and_gets_a_new_code(): void
    {
        $user = $this->unverified('customer');
        $old = EmailOtp::query()->create(['user_id' => $user->id, 'code' => Hash::make('111111'), 'attempts' => 0, 'expires_at' => now()->addMinutes(10)]);

        $this->postJson('/api/auth/customer/register', $this->payload())->assertCreated();

        $this->assertSame(1, User::withTrashed()->where('email', 'again@example.com')->count());
        $fresh = $user->fresh();
        $this->assertSame('Re Register', $fresh->name);
        $this->assertSame('+967770000002', $fresh->phone);
        $this->assertTrue(Hash::check('Password123', $fresh->password));
        $this->assertNull($fresh->email_verified_at);
        $this->assertNotNull($old->fresh()->invalidated_at);
        $this->assertSame(1, EmailOtp::where('user_id', $user->id)->whereNull('invalidated_at')->count());
        Mail::assertSent(VerificationOtpMail::class);
    }

    public function test_verified_email_is_still_rejected_with_an_arabic_message(): void
    {
        $this->unverified('customer', ['email_verified_at' => now()]);

        $this->postJson('/api/auth/customer/register', $this->payload())
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'البريد الإلكتروني مسجل ومفعل بالفعل، يرجى تسجيل الدخول مباشرة.');
    }

    public function test_verified_phone_is_still_rejected(): void
    {
        $this->unverified('customer', ['email' => 'someone@example.com', 'phone' => '+967770000002', 'email_verified_at' => now()]);

        $this->postJson('/api/auth/customer/register', $this->payload())
            ->assertStatus(422)
            ->assertJsonPath('errors.phone.0', 'رقم الجوال مسجل ومفعل بالفعل بحساب آخر.');
    }

    public function test_unverified_supplier_registering_again_as_customer_is_refused_and_store_kept(): void
    {
        $user = $this->unverified('supplier');
        Store::query()->create(['user_id' => $user->id, 'company_name' => 'Pending', 'store_type' => 'retailer', 'address_details' => 'x', 'approval_status' => 'pending']);

        $this->postJson('/api/auth/customer/register', $this->payload())
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertTrue($user->fresh()->hasRole('supplier'));
        $this->assertDatabaseHas('stores', ['user_id' => $user->id, 'company_name' => 'Pending']);
    }

    public function test_unverified_supplier_registering_again_replaces_the_old_document(): void
    {
        $user = $this->unverified('supplier');
        Storage::disk('supabase_private')->put('stores/commercial-documents/old.pdf', 'old');
        Store::query()->create(['user_id' => $user->id, 'company_name' => 'Old Name', 'store_type' => 'retailer', 'address_details' => 'x', 'approval_status' => 'pending', 'commercial_file_path' => 'stores/commercial-documents/old.pdf']);

        $this->post('/api/auth/supplier/register', $this->payload($this->supplierFields()), ['Accept' => 'application/json'])
            ->assertCreated();

        $store = Store::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Solar Supplier', $store->company_name);
        $this->assertSame('pending', $store->approval_status);
        $this->assertNotSame('stores/commercial-documents/old.pdf', $store->commercial_file_path);
        Storage::disk('supabase_private')->assertExists($store->commercial_file_path);
        Storage::disk('supabase_private')->assertMissing('stores/commercial-documents/old.pdf');
        $this->assertSame(1, Store::count());
    }

    public function test_failed_re_registration_keeps_the_old_document(): void
    {
        $user = $this->unverified('supplier');
        Storage::disk('supabase_private')->put('stores/commercial-documents/old.pdf', 'old');
        Store::query()->create(['user_id' => $user->id, 'company_name' => 'Old Name', 'store_type' => 'retailer', 'address_details' => 'x', 'approval_status' => 'pending', 'commercial_file_path' => 'stores/commercial-documents/old.pdf']);
        // الجوال الجديد يملكه حساب آخر غير مفعّل لديه بيانات → يُرفض التسجيل داخل المعاملة
        $holder = $this->unverified('customer', ['email' => 'holder@example.com', 'phone' => '+967770000002']);
        Store::query()->create(['user_id' => $holder->id, 'company_name' => 'Holder', 'store_type' => 'retailer', 'address_details' => 'x', 'approval_status' => 'pending']);

        $this->post('/api/auth/supplier/register', $this->payload($this->supplierFields()), ['Accept' => 'application/json'])
            ->assertStatus(422);

        Storage::disk('supabase_private')->assertExists('stores/commercial-documents/old.pdf');
        $this->assertSame('stores/commercial-documents/old.pdf', Store::where('user_id', $user->id)->value('commercial_file_path'));
        $this->assertSame(['stores/commercial-documents/old.pdf'], Storage::disk('supabase_private')->allFiles('stores/commercial-documents'));
    }
}
