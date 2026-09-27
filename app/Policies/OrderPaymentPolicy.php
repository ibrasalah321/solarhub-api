<?php

namespace App\Policies;

use App\Models\OrderPayment;
use App\Models\User;

class OrderPaymentPolicy
{
    public function view(User $user, OrderPayment $orderPayment): bool
    {
        return $orderPayment->order !== null
            && $orderPayment->order->customer_id === $user->id;
    }
}
