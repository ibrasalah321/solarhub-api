<?php

namespace App\Services\Rating;

use App\Models\OrderStore;
use App\Models\StoreRating;
use App\Models\User;

class StoreRatingService
{
    private const RELATIONS = ['customer', 'store'];

    /**
     * Create a rating for a completed order-store branch.
     */
    public function create(User $customer, array $data): StoreRating
    {
        $orderStore = OrderStore::query()->findOrFail($data['order_store_id']);

        abort_unless(
            $orderStore->order->customer_id === $customer->id,
            403,
            'You are not allowed to rate this order.'
        );

        abort_unless(
            $orderStore->status === 'completed',
            422,
            'You can only rate a store after the order has been completed.'
        );

        $rating = StoreRating::create([
            'order_store_id' => $orderStore->id,
            'customer_id' => $customer->id,
            'store_id' => $orderStore->store_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_approved' => true,
        ]);

        return $rating->load(self::RELATIONS);
    }

    /**
     * Update an existing rating owned by the authenticated customer.
     */
    public function update(User $customer, StoreRating $rating, array $data): StoreRating
    {
        $this->ensureOwnership($customer, $rating);

        $rating->update($data);

        return $rating->load(self::RELATIONS);
    }

    /**
     * Delete a rating owned by the authenticated customer.
     */
    public function delete(User $customer, StoreRating $rating): void
    {
        $this->ensureOwnership($customer, $rating);

        $rating->delete();
    }

    private function ensureOwnership(User $customer, StoreRating $rating): void
    {
        abort_unless(
            $rating->customer_id === $customer->id,
            403,
            'You are not allowed to manage this rating.'
        );
    }
}
