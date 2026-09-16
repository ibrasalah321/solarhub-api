<?php

namespace App\Services;

use App\Models\EngineerProfile;
use App\Models\Offer;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OfferService
{
    public function __construct(
        private readonly SupabaseStorageService $storageService) {
    }

    public function create(EngineerProfile $engineer,ServiceRequest $serviceRequest,array $data): Offer {

        abort_unless(
            $engineer->approval_status === 'approved',
            403,
            'Only approved engineers can submit offers.'
        );

        abort_unless(
            $serviceRequest->status === 'open_for_bids',
            422,
            'Offers can only be submitted to open service requests.'
        );

        $alreadySubmitted = Offer::query()
            ->where('service_request_id', $serviceRequest->id)
            ->where('engineer_id', $engineer->id)
            ->exists();

        abort_if(
            $alreadySubmitted,
            422,
            'You have already submitted an offer for this service request.'
        );

        if (isset($data['proposal'])) {
            $data['proposal_file'] =
                $this->storageService->uploadPrivate(
                    $data['proposal'],
                    "engineers/{$engineer->id}/offers"
                );

            unset($data['proposal']);
        }

        $data['service_request_id'] = $serviceRequest->id;
        $data['engineer_id'] = $engineer->id;
        $data['status'] = 'pending';

        $offer = Offer::create($data);

        return $offer->load([
            'engineer.user',
            'serviceRequest',
        ]);
    }

    public function getEngineerOffers(EngineerProfile $engineer) {
        return $engineer->offers()
            ->with([
                'serviceRequest.serviceType',
                'serviceRequest.governorate',
            ])
            ->latest()
            ->paginate(10);
    }

    public function update(EngineerProfile $engineer,Offer $offer,array $data): Offer {

        $this->ensureEngineerOwnership(
            $engineer,
            $offer
        );

        $offer->load('serviceRequest');

        abort_unless(
            $offer->serviceRequest->status === 'open_for_bids',
            422,
            'This offer can no longer be updated.'
        );

        abort_unless(
            $offer->status === 'pending',
            422,
            'Only pending offers can be updated.'
        );

        if (isset($data['proposal'])) {
            $oldFile = $offer->proposal_file;

            $data['proposal_file'] =
                $this->storageService->uploadPrivate(
                    $data['proposal'],
                    "engineers/{$engineer->id}/offers"
                );

            unset($data['proposal']);

            $this->storageService->deletePrivate($oldFile);
        }

        $offer->update($data);

        return $offer->load([
            'engineer.user',
            'serviceRequest',
        ]);
    }

    public function delete(EngineerProfile $engineer,Offer $offer): void {

        $this->ensureEngineerOwnership(
            $engineer,
            $offer
        );

        $offer->load('serviceRequest');

        abort_unless(
            $offer->serviceRequest->status === 'open_for_bids',
            422,
            'This offer can no longer be deleted.'
        );

        abort_unless(
            $offer->status === 'pending',
            422,
            'Only pending offers can be deleted.'
        );

        if ($offer->proposal_file) {
            $this->storageService->deletePrivate(
                $offer->proposal_file
            );
        }

        $offer->delete();
    }

    private function ensureEngineerOwnership(EngineerProfile $engineer,Offer $offer): void {
        abort_unless(
            $offer->engineer_id === $engineer->id,
            403,
            'You are not allowed to manage this offer.'
        );
    }

    public function getRequestOffers(User $customer,ServiceRequest $serviceRequest) {
        $this->ensureCustomerOwnership(
            $customer,
            $serviceRequest
        );

        return $serviceRequest->offers()
            ->with([
                'engineer.user',
                'engineer.specializations',
            ])
            ->latest()
            ->get();
    }

    private function ensureCustomerOwnership(User $customer,ServiceRequest $serviceRequest): void {
        abort_unless(
            $serviceRequest->customer_id === $customer->id,
            403,
            'You are not allowed to manage this service request.'
        );
    }

    public function accept(User $customer,Offer $offer): Offer {

        return DB::transaction(function () use (
            $customer,
            $offer
        ) {
            $serviceRequest = ServiceRequest::query()
                ->lockForUpdate()
                ->findOrFail($offer->service_request_id);

            $this->ensureCustomerOwnership(
                $customer,
                $serviceRequest
            );

            abort_unless(
                $serviceRequest->status === 'open_for_bids',
                422,
                'This service request is no longer accepting offers.'
            );

            abort_unless(
                $offer->status === 'pending',
                422,
                'Only pending offers can be accepted.'
            );

            Offer::query()
                ->where('service_request_id', $serviceRequest->id)
                ->where('id', '!=', $offer->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                ]);

            $offer->update([
                'status' => 'accepted',
            ]);

            $serviceRequest->update([
                'status' => 'awarded',
            ]);

            return $offer->load([
                'engineer.user',
                'serviceRequest',
            ]);
        });
    }
}