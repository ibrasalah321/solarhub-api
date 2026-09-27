<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;

/**
 * Ownership (IDOR) gate for offers.
 *
 * - The engineer who authored an offer may edit/withdraw it.
 * - The customer who owns the underlying service request may view and accept
 *   offers made on it.
 *
 * Admins bypass via Gate::before.
 */
class OfferPolicy
{
    public function view(User $user, Offer $offer): bool
    {
        return $this->ownedByEngineer($user, $offer)
            || $this->ownedByCustomer($user, $offer);
    }

    public function update(User $user, Offer $offer): bool
    {
        return $this->ownedByEngineer($user, $offer);
    }

    public function delete(User $user, Offer $offer): bool
    {
        return $this->ownedByEngineer($user, $offer);
    }

    public function accept(User $user, Offer $offer): bool
    {
        return $this->ownedByCustomer($user, $offer);
    }

    private function ownedByEngineer(User $user, Offer $offer): bool
    {
        return $offer->engineer !== null
            && $offer->engineer->user_id === $user->id;
    }

    private function ownedByCustomer(User $user, Offer $offer): bool
    {
        return $offer->serviceRequest !== null
            && $offer->serviceRequest->customer_id === $user->id;
    }
}
