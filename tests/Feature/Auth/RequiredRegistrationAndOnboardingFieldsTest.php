<?php

namespace Tests\Feature\Auth;

use App\Mail\VerificationOtpMail;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RequiredRegistrationAndOnboardingFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public static function invalidRequiredValues(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'whitespace only' => ['   '],
        ];
    }

    #[DataProvider('invalidRequiredValues')]
    public function test_registration_rejects_missing_or_blank_common_fields(mixed $invalidValue): void
    {
        foreach (['name', 'email', 'phone', 'password', 'password_confirmation'] as $field) {
            $payload = $this->validRegistrationPayload();
            $payload[$field] = $invalidValue;

            $this->postJson('/api/auth/register', $payload)
                ->assertUnprocessable()
                ->assertJsonValidationErrors($field);
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_each_missing_common_field_before_user_creation(): void
    {
        foreach (['name', 'email', 'phone', 'password', 'password_confirmation'] as $field) {
            $payload = $this->validRegistrationPayload();
            unset($payload[$field]);

            $this->postJson('/api/auth/register', $payload)
                ->assertUnprocessable()
                ->assertJsonValidationErrors($field);
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_customer_registration_succeeds_without_professional_fields(): void
    {
        Mail::fake();

        $this->postJson('/api/auth/register', $this->validRegistrationPayload())
            ->assertCreated()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('users', [
            'email' => 'required-fields@example.com',
        ]);
        Mail::assertSent(VerificationOtpMail::class);
    }

    public function test_engineer_onboarding_rejects_missing_null_empty_and_whitespace_fields(): void
    {
        Sanctum::actingAs(User::factory()->engineer()->create());

        foreach (['license_number', 'cv'] as $field) {
            foreach ($this->invalidValuesIncludingMissing() as $case => $invalidValue) {
                $payload = $this->validEngineerPayload();

                if ($case === 'missing') {
                    unset($payload[$field]);
                } else {
                    $payload[$field] = $invalidValue;
                }

                $this->post('/api/engineer/onboarding', $payload, [
                    'Accept' => 'application/json',
                ])->assertUnprocessable()
                    ->assertJsonValidationErrors($field);
            }
        }

        $this->assertDatabaseCount('engineer_profile', 0);
    }

    public function test_valid_engineer_onboarding_is_created_pending(): void
    {
        Storage::fake('supabase_private');
        $user = User::factory()->engineer()->create();
        Sanctum::actingAs($user);

        $this->post('/api/engineer/onboarding', $this->validEngineerPayload(), [
            'Accept' => 'application/json',
        ])->assertOk()
            ->assertJsonPath('data.approval_status', 'pending');

        $this->assertDatabaseHas('engineer_profile', [
            'user_id' => $user->id,
            'license_number' => 'ENG-12345',
            'approval_status' => 'pending',
        ]);
    }

    public function test_store_onboarding_rejects_missing_null_empty_and_whitespace_fields(): void
    {
        Sanctum::actingAs(User::factory()->supplier()->create());

        foreach ([
            'company_name',
            'commercial_registry',
            'commercial_file',
            'address_details',
            'store_type',
        ] as $field) {
            foreach ($this->invalidValuesIncludingMissing() as $case => $invalidValue) {
                $payload = $this->validStorePayload();

                if ($case === 'missing') {
                    unset($payload[$field]);
                } else {
                    $payload[$field] = $invalidValue;
                }

                $this->post('/api/stores', $payload, [
                    'Accept' => 'application/json',
                ])->assertUnprocessable()
                    ->assertJsonValidationErrors($field);
            }
        }

        $this->assertDatabaseCount('stores', 0);
    }

    public function test_valid_store_onboarding_is_created_pending(): void
    {
        Storage::fake('supabase_private');
        $user = User::factory()->supplier()->create();
        Sanctum::actingAs($user);

        $this->post('/api/stores', $this->validStorePayload(), [
            'Accept' => 'application/json',
        ])->assertCreated()
            ->assertJsonPath('data.approval_status', 'pending');

        $this->assertDatabaseHas('stores', [
            'user_id' => $user->id,
            'company_name' => 'Solar Trade',
            'commercial_registry' => 'CR-98765',
            'address_details' => 'Cairo, Egypt',
            'store_type' => 'retailer',
            'approval_status' => 'pending',
        ]);
    }

    private function validRegistrationPayload(): array
    {
        return [
            'name' => 'Required Fields User',
            'email' => 'required-fields@example.com',
            'phone' => '+201001234567',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'customer',
        ];
    }

    private function validEngineerPayload(): array
    {
        return [
            'license_number' => 'ENG-12345',
            'cv' => UploadedFile::fake()->create(
                'cv.pdf',
                100,
                'application/pdf'
            ),
        ];
    }

    private function validStorePayload(): array
    {
        return [
            'company_name' => 'Solar Trade',
            'commercial_registry' => 'CR-98765',
            'commercial_file' => UploadedFile::fake()->create(
                'registry.pdf',
                100,
                'application/pdf'
            ),
            'address_details' => 'Cairo, Egypt',
            'store_type' => 'retailer',
        ];
    }

    private function invalidValuesIncludingMissing(): array
    {
        return [
            'missing' => null,
            'null' => null,
            'empty string' => '',
            'whitespace only' => '   ',
        ];
    }
}
