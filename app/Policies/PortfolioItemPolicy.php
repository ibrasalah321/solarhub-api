<?php

namespace App\Policies;

use App\Models\PortfolioItem;
use App\Models\User;

/**
 * Ownership (IDOR) gate for portfolio items. An engineer may only manage
 * portfolio items attached to their own profile. Admins bypass via Gate::before.
 */
class PortfolioItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PortfolioItem $portfolioItem): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('portfolio-items.create');
    }

    public function update(User $user, PortfolioItem $portfolioItem): bool
    {
        return $this->owns($user, $portfolioItem);
    }

    public function delete(User $user, PortfolioItem $portfolioItem): bool
    {
        return $this->owns($user, $portfolioItem);
    }

    private function owns(User $user, PortfolioItem $portfolioItem): bool
    {
        return $portfolioItem->engineer !== null
            && $portfolioItem->engineer->user_id === $user->id;
    }
}
