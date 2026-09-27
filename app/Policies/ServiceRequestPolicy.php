<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

/**
 * Ownership (IDOR) gate for service requests. A customer may only view/edit/
 * cancel their own service requests. Admins bypass via Gate::before.
 */
class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('service-requests.create');
    }

    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->customer_id === $user->id;
    }

    public function cancel(User $user, ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->customer_id === $user->id;
    }

    public function delete(User $user, ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->customer_id === $user->id;
    }
}
