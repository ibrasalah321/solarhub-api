<?php

namespace Tests\Feature\Auth;

use App\Mail\VerificationOtpMail;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Mail::fake();
        Storage::fake('supabase_private');
    }

    public function test_customer_registration_uses_role_from_endpoint(): void
    {
        $response = $this->postJson(
            '/api/auth/customer/register',
            $this->accountPayload('customer@example.com', '+201000000001')
        );

        $response->assertCreated();

        $user = User::query()->where('email', 'customer@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('customer'));
        $this->assertNull($user->engineerProfile);
        $this->assertNull($user->store);
        Mail::assertSent(VerificationOtpMail::class);
    }

    public function test_role_cannot_be_overridden_in_registration_body(): void
    {
        $payload = $this->accountPayload(
            'override@example.com',
            '+201000000002'
        );
        $payload['role'] = 'supplier';

        $this->postJson('/api/auth/customer/register', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'override@example.com',
        ]);
    }

    public function test_engineer_registration_creates_pending_application(): void
    {
        $payload = $this->accountPayload(
            'engineer@example.com',
            '+201000000003'
        ) + $this->engineerPayload();

        $this->post('/api/auth/engineer/register', $payload, [
            'Accept' => 'application/json',
        ])->assertCreated();

        $user = User::query()->where('email', 'engineer@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('engineer'));
        $this->assertDatabaseHas('engineer_profile', [
            'user_id' => $user->id,
            'license_number' => 'ENG-100',
            'approval_status' => 'pending',
        ]);
        Storage::disk('supabase_private')->assertExists(
            $user->engineerProfile->cv_path
        );
    }

    public function test_supplier_registration_creates_pending_application(): void
    {
        $payload = $this->accountPayload(
            'supplier@example.com',
            '+201000000004'
        ) + $this->supplierPayload();

        $this->post('/api/auth/supplier/register', $payload, [
            'Accept' => 'application/json',
        ])->assertCreated();

        $user = User::query()->where('email', 'supplier@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('supplier'));
        $this->assertDatabaseHas('stores', [
            'user_id' => $user->id,
            'company_name' => 'Solar Supplier',
            'commercial_registry' => 'CR-100',
            'address_details' => 'Cairo',
            'store_type' => 'retailer',
            'approval_status' => 'pending',
        ]);
        Storage::disk('supabase_private')->assertExists(
            $user->store->commercial_file_path
        );
    }

    public function test_every_role_rejects_missing_null_empty_and_whitespace_account_fields(): void
    {
        foreach (['customer', 'engineer', 'supplier'] as $role) {
            foreach ([
                'name',
                'email',
                'phone',
                'password',
                'password_confirmation',
            ] as $field) {
                foreach ($this->invalidValues() as $case => $value) {
                    $payload = $this->payloadForRole($role);

                    if ($case === 'missing') {
                        unset($payload[$field]);
                    } else {
                        $payload[$field] = $value;
                    }

                    $this->post(
                        "/api/auth/{$role}/register",
                        $payload,
                        ['Accept' => 'application/json']
                    )->assertUnprocessable()
                        ->assertJsonValidationErrors($field);
                }
            }
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_professional_fields_reject_missing_null_empty_and_whitespace(): void
    {
        $fieldsByRole = [
            'engineer' => ['license_number', 'cv'],
            'supplier' => [
                'company_name',
                'commercial_registry',
                'commercial_file',
                'address_details',
                'store_type',
            ],
        ];

        foreach ($fieldsByRole as $role => $fields) {
            foreach ($fields as $field) {
                foreach ($this->invalidValues() as $case => $value) {
                    $payload = $this->payloadForRole($role);

                    if ($case === 'missing') {
                        unset($payload[$field]);
                    } else {
                        $payload[$field] = $value;
                    }

                    $this->post(
                        "/api/auth/{$role}/register",
                        $payload,
                        ['Accept' => 'application/json']
                    )->assertUnprocessable()
                        ->assertJsonValidationErrors($field);
                }
            }
        }

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('engineer_profile', 0);
        $this->assertDatabaseCount('stores', 0);
    }

    public function test_role_login_issues_token_only_for_matching_role(): void
    {
        $customer = User::factory()->create([
            'email' => 'login-customer@example.com',
            'phone' => '+201000000010',
            'password' => Hash::make('Password123'),
        ]);
        $customer->syncRoles(['customer']);

        $this->postJson('/api/auth/customer/login', [
            'login' => 'login-customer@example.com',
            'password' => 'Password123',
        ])->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->postJson('/api/auth/engineer/login', [
            'login' => 'login-customer@example.com',
            'password' => 'Password123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('login');

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_unverified_account_cannot_use_role_login(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => Hash::make('Password123'),
        ]);
        $user->syncRoles(['engineer']);

        $this->postJson('/api/auth/engineer/login', [
            'login' => $user->email,
            'password' => 'Password123',
        ])->assertForbidden();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    private function payloadForRole(string $role): array
    {
        $payload = $this->accountPayload(
            "{$role}-validation@example.com",
            match ($role) {
                'customer' => '+201000000020',
                'engineer' => '+201000000021',
                'supplier' => '+201000000022',
            }
        );

        return match ($role) {
            'engineer' => $payload + $this->engineerPayload(),
            'supplier' => $payload + $this->supplierPayload(),
            default => $payload,
        };
    }

    private function accountPayload(string $email, string $phone): array
    {
        return [
            'name' => 'Role Test User',
            'email' => $email,
            'phone' => $phone,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];
    }

    private function engineerPayload(): array
    {
        return [
            'license_number' => 'ENG-100',
            'cv' => UploadedFile::fake()->create(
                'cv.pdf',
                100,
                'application/pdf'
            ),
        ];
    }

    private function supplierPayload(): array
    {
        return [
            'company_name' => 'Solar Supplier',
            'commercial_registry' => 'CR-100',
            'commercial_file' => UploadedFile::fake()->create(
                'commercial.pdf',
                100,
                'application/pdf'
            ),
            'address_details' => 'Cairo',
            'store_type' => 'retailer',
        ];
    }

    private function invalidValues(): array
    {
        return [
            'missing' => null,
            'null' => null,
            'empty' => '',
            'whitespace' => '   ',
        ];
    }
}
