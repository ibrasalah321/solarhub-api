<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestService
{
    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    public function create(User $customer,array $data): ServiceRequest {

        if (isset($data['attachment'])) {
            $data['attachment_file'] =
                $this->storageService->uploadPrivate(
                    $data['attachment'],
                    "service-requests/{$customer->id}/attachments"
                );

            unset($data['attachment']);
        }

        $data['customer_id'] = $customer->id;
        $data['status'] = 'open_for_bids';

        $serviceRequest = ServiceRequest::create($data);

        return $serviceRequest->load([
            'customer',
            'serviceType',
            'governorate',
        ]);
    }
    public function getCustomerRequests(User $customer,array $filters = []) {
        return ServiceRequest::query()
            ->where('customer_id', $customer->id)
            ->with([
                'serviceType',
                'governorate',
            ])
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }
    public function getOpenRequests(array $filters = [])
    {
        return ServiceRequest::query()
            ->where('status', 'open_for_bids')
            ->with([
                'customer',
                'serviceType',
                'governorate',
            ])
            ->when($filters['service_type_id'] ?? null, function ($query, $serviceTypeId) {
                $query->where('service_type_id', $serviceTypeId);
            })
            ->when($filters['governorate_id'] ?? null, function ($query, $governorateId) {
                $query->where('governorate_id', $governorateId);
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function findRequest(int $id): ServiceRequest
    {
        return ServiceRequest::query()
            ->with([
                'customer',
                'serviceType',
                'governorate',
                'offers',
            ])
            ->findOrFail($id);
    }

    public function update(User $customer,ServiceRequest $serviceRequest,array $data): ServiceRequest {

        $this->ensureCustomerOwnership(
            $customer,
            $serviceRequest
        );

        abort_unless(
            $serviceRequest->status === 'open_for_bids',
            422,
            'Only open service requests can be updated.'
        );

        if (isset($data['attachment'])) {
            $oldAttachment = $serviceRequest->attachment_file;

            $data['attachment_file'] =
                $this->storageService->uploadPrivate(
                    $data['attachment'],
                    "service-requests/{$customer->id}/attachments"
                );

            unset($data['attachment']);

            $this->storageService->deletePrivate($oldAttachment);
        }

        $serviceRequest->update($data);

        return $serviceRequest->load([
            'customer',
            'serviceType',
            'governorate',
        ]);
    }

    public function cancel(User $customer,ServiceRequest $serviceRequest): ServiceRequest {

        $this->ensureCustomerOwnership(
            $customer,
            $serviceRequest
        );

        abort_unless(
            $serviceRequest->status === 'open_for_bids',
            422,
            'Only open service requests can be cancelled.'
        );

        $serviceRequest->update([
            'status' => 'cancelled',
        ]);

        return $serviceRequest->load([
            'customer',
            'serviceType',
            'governorate',
        ]);
    }


    private function ensureCustomerOwnership(User $customer,ServiceRequest $serviceRequest): void {
        abort_unless(
            $serviceRequest->customer_id === $customer->id,
            403,
            'You are not allowed to manage this service request.'
        );
    }
}