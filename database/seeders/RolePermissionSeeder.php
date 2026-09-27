<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
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
            'quote-requests.reject',
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

    private array $adminOnlyPermissions = [
        'engineers.view-pending',
        'engineers.approve',
        'engineers.reject',
        'stores.view-pending',
        'stores.approve',
        'stores.reject',
        'stores.delete',
        'engineer-profiles.approve',
        'catalog.manage',
        'users.manage',
        'settings.manage',
        'notification-templates.manage',
        'wallet-providers.manage',
        'order-payments.verify',
        'store-payouts.manage',
    ];

    public function run(): void
    {
        $registrar = App::make(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        $guard = 'web';

        $allPermissions = array_values(array_unique(array_merge(
            $this->adminOnlyPermissions,
            ...array_values($this->rolePermissions)
        )));

        foreach ($allPermissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        $admin = Role::findOrCreate('admin', $guard);
        $admin->syncPermissions(
            Permission::query()->where('guard_name', $guard)->get()
        );

        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, $guard);
            $role->syncPermissions($permissions);
        }

        $registrar->forgetCachedPermissions();
    }
}
