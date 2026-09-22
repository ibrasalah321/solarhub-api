<?php

namespace App\Services\Auth\Admin;

use App\Models\EngineerProfile;
use App\Models\Store;
use Illuminate\Validation\ValidationException;

class AdminApprovalService
{
    public function pendingEngineers()
    {
        return EngineerProfile::query()
            ->with('user')
            ->where('approval_status', 'pending')
            ->paginate(15);
    }

    public function approveEngineer(
        EngineerProfile $engineer
    ): EngineerProfile {
        if ($engineer->approval_status !== 'pending') {
            throw ValidationException::withMessages([
                'approval_status' => [
                    'Only pending engineer applications can be approved.',
                ],
            ]);
        }

        $engineer->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return $engineer;
    }

    public function rejectEngineer(
        EngineerProfile $engineer,
        string $rejectionReason
    ): EngineerProfile {
        if ($engineer->approval_status !== 'pending') {
            throw ValidationException::withMessages([
                'approval_status' => [
                    'Only pending engineer applications can be rejected.',
                ],
            ]);
        }

        $engineer->update([
            'approval_status' => 'rejected',
            'approved_at' => null,
            'rejection_reason' => $rejectionReason,
        ]);

        return $engineer;
    }

    public function pendingStores()
    {
        return Store::query()
            ->with('user')
            ->where('approval_status', 'pending')
            ->paginate(15);
    }

    public function approveStore(Store $store): Store
    {
        if ($store->approval_status !== 'pending') {
            throw ValidationException::withMessages([
                'approval_status' => [
                    'Only pending store applications can be approved.',
                ],
            ]);
        }

        $store->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return $store;
    }

    public function rejectStore(
        Store $store,
        string $rejectionReason
    ): Store {
        if ($store->approval_status !== 'pending') {
            throw ValidationException::withMessages([
                'approval_status' => [
                    'Only pending store applications can be rejected.',
                ],
            ]);
        }

        $store->update([
            'approval_status' => 'rejected',
            'approved_at' => null,
            'rejection_reason' => $rejectionReason,
        ]);

        return $store;
    }
}