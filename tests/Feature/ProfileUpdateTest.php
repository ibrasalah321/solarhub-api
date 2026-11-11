<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->customer = User::factory()->create(['password' => Hash::make('OldPass@123')]);
        $this->customer->syncRoles(['customer']);
        Sanctum::actingAs($this->customer);
    }

    public function test_address_text_in_coordinates_is_rejected_with_422_not_500(): void
    {
        $this->putJson('/api/my/profile', [
            'name' => 'عميل',
            'default_coordinates' => 'صنعاء - مركز المحافظة',
        ])->assertStatus(422)->assertJsonValidationErrors('default_coordinates');
    }

    public function test_out_of_range_point_is_rejected(): void
    {
        $this->putJson('/api/my/profile', ['default_coordinates' => 'POINT(200 95)'])
            ->assertStatus(422)->assertJsonValidationErrors('default_coordinates');
    }

    public function test_valid_point_is_accepted(): void
    {
        $this->putJson('/api/my/profile', ['default_coordinates' => 'POINT(44.191 15.3694)'])
            ->assertOk();
    }

    public function test_password_and_profile_can_be_changed_without_coordinates(): void
    {
        $this->putJson('/api/my/profile', [
            'name' => 'اسم جديد',
            'current_password' => 'OldPass@123',
            'password' => 'NewPass@12345',
            'password_confirmation' => 'NewPass@12345',
        ])->assertOk();

        $this->customer->refresh();
        $this->assertSame('اسم جديد', $this->customer->name);
        $this->assertTrue(Hash::check('NewPass@12345', $this->customer->password));
    }

    public function test_database_errors_never_leak_sql_even_in_debug(): void
    {
        config(['app.debug' => true]);
        Route::middleware('api')->get('/api/__test-query-exception', function () {
            throw new QueryException('pgsql', 'update "users" set "password" = ? where "id" = ?', ['$2y$12$secrethash', 1], new \RuntimeException('invalid geometry'));
        });

        $response = $this->getJson('/api/__test-query-exception');

        $response->assertStatus(500);
        $body = $response->getContent();
        $this->assertStringNotContainsString('update "users"', $body);
        $this->assertStringNotContainsString('secrethash', $body);
        $this->assertStringNotContainsString('invalid geometry', $body);
    }
}
