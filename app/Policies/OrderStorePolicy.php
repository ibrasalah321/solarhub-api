<?php

namespace App\Policies;

use App\Models\OrderStore;
use App\Models\User;

class OrderStorePolicy
{
    public function view(User $user, OrderStore $orderStore): bool
    {
        return $orderStore->order?->customer_id === $user->id
            || $orderStore->store?->user_id === $user->id;
    }

    public function updateStatus(User $user, OrderStore $orderStore): bool
    {
        return $orderStore->store?->user_id === $user->id;
    }

    public function confirmDelivery(User $user, OrderStore $orderStore): bool
    {
        return $orderStore->order?->customer_id === $user->id;
    }
}
