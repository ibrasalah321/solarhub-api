<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlatformCurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);
        Sanctum::actingAs($admin);
    }

    public function test_only_yemeni_rial_is_accepted(): void
    {
        foreach (['SAR', 'USD'] as $currency) {
            $this->putJson('/api/settings', ['currency' => $currency])
                ->assertStatus(422)
                ->assertJsonValidationErrors('currency');
        }
    }

    public function test_yer_is_accepted(): void
    {
        $this->putJson('/api/settings', ['currency' => 'YER'])->assertOk();
    }
}
