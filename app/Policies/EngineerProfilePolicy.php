<?php

namespace App\Policies;

use App\Models\EngineerProfile;
use App\Models\User;

/**
 * Ownership (IDOR) gate for engineer profiles. An engineer may only edit their
 * own profile. Approval of a profile is an admin capability. Admins bypass
 * ownership via Gate::before.
 */
class EngineerProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, EngineerProfile $engineerProfile): bool
    {
        return true;
    }

    public function update(User $user, EngineerProfile $engineerProfile): bool
    {
        return $engineerProfile->user_id === $user->id;
    }

    public function delete(User $user, EngineerProfile $engineerProfile): bool
    {
        return $engineerProfile->user_id === $user->id;
    }

    public function approve(User $user, EngineerProfile $engineerProfile): bool
    {
        return $user->can('engineer-profiles.approve');
    }
}
