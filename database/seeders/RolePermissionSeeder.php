<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the four platform roles and their `resource.action` permissions.
 *
 * Design rules:
 *  - Exactly four roles: admin, customer, engineer, supplier (guard: web).
 *  - Permissions are role CAPABILITIES only. They never encode approval state
 *    or ownership — those are handled by EnsureProfileIsApproved middleware and
 *    Policies respectively.
 *  - The admin role is granted every permission.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Permissions grouped by the role that receives them (besides admin, which
     * receives all of them).
     *
     * @var array<string, list<string>>
     */
    private array $rolePermissions = [
        'customer' => [
            'orders.view',
            'orders.create',
            'orders.cancel',
            'service-requests.create',
            'service-requests.update',
            'service-requests.cancel',
            'quote-requests.create',
            'quote-requests.accept',
            'offers.accept',
            'store-ratings.create',
            'store-ratings.update',
            'store-ratings.delete',
            'engineer-ratings.create',
            'engineer-ratings.update',
            'engineer-ratings.delete',
        ],
        'engineer' => [
            'engineer-profiles.update',
            'engineer-certificates.create',
            'engineer-certificates.delete',
            'portfolio-items.create',
            'portfolio-items.update',
            'portfolio-items.delete',
            'service-requests.view-open',
            'offers.create',
            'offers.update',
            'offers.delete',
        ],
        'supplier' => [
            'stores.create',
            'stores.update',
            'store-products.create',
            'store-products.update',
            'store-products.delete',
            'quote-requests.respond',
            'orders.manage-status',
        ],
    ];

    /**
     * Permissions that only administrators hold.
     *
     * @var list<string>
     */
    private array $adminOnlyPermissions = [
        'stores.approve',
        'stores.delete',
        'engineer-profiles.approve',
        'catalog.manage',
        'users.manage',
        'settings.manage',
        'notification-templates.manage',
    ];

    public function run(): void
    {
        // Ensure a clean permission cache before (re)seeding.
        App::make(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // 1. Create every permission.
        $allPermissions = $this->adminOnlyPermissions;
        foreach ($this->rolePermissions as $permissions) {
            $allPermissions = array_merge($allPermissions, $permissions);
        }
        $allPermissions = array_values(array_unique($allPermissions));

        foreach ($allPermissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        // 2. Create roles and attach their permissions.
        $admin = Role::findOrCreate('admin', $guard);
        $admin->syncPermissions(Permission::where('guard_name', $guard)->get());

        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, $guard);
            $role->syncPermissions($permissions);
        }

        // 3. Refresh the cache so the new state is immediately available.
        App::make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
