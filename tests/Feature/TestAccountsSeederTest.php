<?php

namespace Tests\Feature;

use App\Models\Governorate;
use App\Models\StoreProduct;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TestAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TestAccountsSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Governorate::query()->create(['name_ar' => 'صنعاء', 'name_en' => 'Sanaa']);
    }

    public function test_seeds_one_ready_account_per_role_and_is_idempotent(): void
    {
        $this->seed(TestAccountsSeeder::class);
        $this->seed(TestAccountsSeeder::class);

        foreach (['customer', 'supplier', 'engineer', 'admin'] as $role) {
            $users = User::where('email', "{$role}@solarhub.test")->get();

            $this->assertCount(1, $users);
            $user = $users->first();
            $this->assertTrue($user->hasRole($role));
            $this->assertNotNull($user->email_verified_at);
            $this->assertSame('active', $user->status);
            $this->assertNotNull($user->governorate_id);
            $this->assertTrue(Hash::check('Test@12345', $user->password));
            $this->assertCount(0, $user->getDirectPermissions());
        }

        $supplier = User::where('email', 'supplier@solarhub.test')->first();
        $this->assertSame('approved', $supplier->store->approval_status);
        $this->assertSame(1, StoreProduct::where('store_id', $supplier->store->id)->count());

        $engineer = User::where('email', 'engineer@solarhub.test')->first();
        $this->assertSame('approved', $engineer->engineerProfile->approval_status);
        $this->assertNotEmpty($engineer->engineerProfile->license_number);
        $this->assertGreaterThan(0, $engineer->engineerProfile->specializations()->count());
    }

    public function test_refuses_to_run_outside_local_and_testing(): void
    {
        $this->app['env'] = 'production';

        // استدعاء مباشر: أمر db:seed نفسه يطلب تأكيداً في production
        $this->app->make(TestAccountsSeeder::class)->run();

        $this->assertSame(0, User::where('email', 'like', '%@solarhub.test')->count());
    }
}
