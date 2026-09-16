<?php

namespace App\Services;

use App\Models\EngineerProfile;
use App\Models\EngineerRating;
use App\Models\ServiceRequest;
use App\Models\User;

class EngineerRatingService
{
    public function create(User $customer,ServiceRequest $serviceRequest,array $data): EngineerRating {

        $this->ensureCustomerOwnership(
            $customer,
            $serviceRequest
        );

        abort_unless(
            $serviceRequest->status === 'completed',
            422,
            'The service request must be completed before it can be rated.'
        );

        $alreadyRated = EngineerRating::query()
            ->where('service_request_id', $serviceRequest->id)
            ->exists();

        abort_if(
            $alreadyRated,
            422,
            'This service request has already been rated.'
        );

        $acceptedOffer = $serviceRequest->offers()
            ->where('status', 'accepted')
            ->first();

        abort_if(
            !$acceptedOffer,
            422,
            'No accepted offer was found for this service request.'
        );

        $rating = EngineerRating::create([
            'service_request_id' => $serviceRequest->id,
            'customer_id' => $customer->id,
            'engineer_id' => $acceptedOffer->engineer_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_approved' => true,
        ]);

        return $rating->load([
            'customer',
            'engineer.user',
        ]);
    }

    public function update(User $customer,EngineerRating $rating,array $data): EngineerRating {

        $this->ensureRatingOwnership(
            $customer,
            $rating
        );

        $rating->update($data);

        return $rating->load([
            'customer',
            'engineer.user',
        ]);
    }

    public function delete(User $customer,EngineerRating $rating): void {

        $this->ensureRatingOwnership(
            $customer,
            $rating
        );

        $rating->delete();
    }

    private function ensureCustomerOwnership(User $customer,ServiceRequest $serviceRequest): void {
        abort_unless(
            $serviceRequest->customer_id === $customer->id,
            403,
            'You are not allowed to rate this service request.'
        );
    }

    private function ensureRatingOwnership(User $customer,EngineerRating $rating): void {
        abort_unless(
            $rating->customer_id === $customer->id,
            403,
            'You are not allowed to manage this rating.'
        );
    }

    public function getEngineerRatings(EngineerProfile $engineer) {
        abort_unless(
            $engineer->approval_status === 'approved',
            404,
            'Engineer not found.'
        );

        return $engineer->ratings()
            ->where('is_approved', true)
            ->with('customer')
            ->latest()
            ->paginate(10);
    }
}