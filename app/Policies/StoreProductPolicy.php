<?php

namespace App\Policies;

use App\Models\StoreProduct;
use App\Models\User;

/**
 * Ownership (IDOR) gate for store products. A supplier may only manage the
 * products that belong to their own store. Admins bypass via Gate::before.
 */
class StoreProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, StoreProduct $storeProduct): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('store-products.create');
    }

    public function update(User $user, StoreProduct $storeProduct): bool
    {
        return $storeProduct->store !== null
            && $storeProduct->store->user_id === $user->id;
    }

    public function delete(User $user, StoreProduct $storeProduct): bool
    {
        return $storeProduct->store !== null
            && $storeProduct->store->user_id === $user->id;
    }
}
