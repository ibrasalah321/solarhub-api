<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

/**
 * Ownership (IDOR) gate for orders. A customer may only view and act on their
 * own orders. Admins bypass via Gate::before.
 */
class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $order->customer_id === $user->id;
    }

    public function update(User $user, Order $order): bool
    {
        return $order->customer_id === $user->id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $order->customer_id === $user->id;
    }

    public function delete(User $user, Order $order): bool
    {
        return $order->customer_id === $user->id;
    }
}
