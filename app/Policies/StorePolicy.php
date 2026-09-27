<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

/**
 * Ownership (IDOR) gate for stores.
 *
 * Role capability ("can a supplier update a store at all") is enforced by
 * Spatie permission middleware on the route. This policy only answers
 * "does THIS store belong to the acting user". Admins bypass via Gate::before.
 */
class StorePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Store $store): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('stores.create');
    }

    public function update(User $user, Store $store): bool
    {
        return $store->user_id === $user->id;
    }

    public function delete(User $user, Store $store): bool
    {
        return $store->user_id === $user->id;
    }

    public function approve(User $user, Store $store): bool
    {
        return $user->can('stores.approve');
    }
}
