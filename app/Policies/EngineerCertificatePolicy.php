<?php

namespace App\Policies;

use App\Models\EngineerCertificate;
use App\Models\User;

/**
 * Ownership (IDOR) gate for engineer certificates. An engineer may only manage
 * certificates attached to their own profile. Admins bypass via Gate::before.
 */
class EngineerCertificatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, EngineerCertificate $engineerCertificate): bool
    {
        return $this->owns($user, $engineerCertificate);
    }

    public function create(User $user): bool
    {
        return $user->can('engineer-certificates.create');
    }

    public function update(User $user, EngineerCertificate $engineerCertificate): bool
    {
        return $this->owns($user, $engineerCertificate);
    }

    public function delete(User $user, EngineerCertificate $engineerCertificate): bool
    {
        return $this->owns($user, $engineerCertificate);
    }

    private function owns(User $user, EngineerCertificate $engineerCertificate): bool
    {
        return $engineerCertificate->engineer !== null
            && $engineerCertificate->engineer->user_id === $user->id;
    }
}
