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
        // العميل صاحب الطلب، أو المهندس صاحب العرض المقبول (يحتاج متابعة الطلب أثناء التنفيذ)
        return $serviceRequest->customer_id === $user->id
            || $this->isAwardedEngineer($user, $serviceRequest);
    }

    /** المهندس صاحب العرض المقبول فقط يبدأ التنفيذ */
    public function start(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->isAwardedEngineer($user, $serviceRequest);
    }

    /** المهندس صاحب العرض المقبول فقط يبلغ بإنهاء العمل */
    public function markDone(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->isAwardedEngineer($user, $serviceRequest);
    }

    /** العميل صاحب الطلب فقط يؤكد الاكتمال */
    public function confirmCompletion(User $user, ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->customer_id === $user->id;
    }

    private function isAwardedEngineer(User $user, ServiceRequest $serviceRequest): bool
    {
        $engineerId = $user->engineerProfile?->id;

        if (! $engineerId) {
            return false;
        }

        return $serviceRequest->offers()
            ->where('status', 'accepted')
            ->where('engineer_id', $engineerId)
            ->exists();
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
