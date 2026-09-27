<?php

namespace App\Policies;

use App\Models\QuoteRequest;
use App\Models\User;

class QuoteRequestPolicy
{
    public function respond(User $user, QuoteRequest $quoteRequest): bool
    {
        return $quoteRequest->storeProduct?->store?->user_id === $user->id;
    }

    public function accept(User $user, QuoteRequest $quoteRequest): bool
    {
        return $quoteRequest->customer_id === $user->id;
    }

    public function reject(User $user, QuoteRequest $quoteRequest): bool
    {
        return $quoteRequest->customer_id === $user->id;
    }
}
