<?php

namespace Tests\Feature\Authorization;

use App\Models\EngineerCertificate;
use App\Models\EngineerProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EngineerCertificateOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('supabase_private');
    }

    public function test_customer_cannot_delete_engineer_certificate(): void
    {
        [, $certificate] = $this->createEngineerWithCertificate();

        $customer = User::factory()->customer()->create();
        Sanctum::actingAs($customer);

        $this->deleteJson("/api/engineer/certificates/{$certificate->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('engineer_certificates', [
            'id' => $certificate->id,
        ]);
    }

    public function test_engineer_cannot_delete_another_engineers_certificate(): void
    {
        [, $certificate] = $this->createEngineerWithCertificate();

        $otherEngineer = User::factory()->engineer()->create();
        EngineerProfile::query()->create([
            'user_id' => $otherEngineer->id,
            'approval_status' => 'pending',
        ]);

        Sanctum::actingAs($otherEngineer);

        $this->deleteJson("/api/engineer/certificates/{$certificate->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('engineer_certificates', [
            'id' => $certificate->id,
        ]);
    }

    public function test_engineer_can_delete_own_certificate(): void
    {
        [$engineer, $certificate] = $this->createEngineerWithCertificate();

        Sanctum::actingAs($engineer);

        $this->deleteJson("/api/engineer/certificates/{$certificate->id}")
            ->assertOk();

        $this->assertDatabaseMissing('engineer_certificates', [
            'id' => $certificate->id,
        ]);
    }

    private function createEngineerWithCertificate(): array
    {
        $engineer = User::factory()->engineer()->create();

        $profile = EngineerProfile::query()->create([
            'user_id' => $engineer->id,
            'approval_status' => 'pending',
        ]);

        $certificate = EngineerCertificate::query()->create([
            'engineer_id' => $profile->id,
            'file_path' => "engineers/{$profile->id}/certificates/test.pdf",
        ]);

        return [$engineer, $certificate];
    }
}
