<?php

namespace App\Services\Order;

use App\Models\QuoteRequest;
use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class QuoteRequestService
{
    private const RELATIONS = ['customer', 'storeProduct.masterProduct', 'storeProduct.store'];

    /**
     * List quote requests submitted by the authenticated customer.
     */
    public function getMyQuoteRequests(User $customer): Collection
    {
        return QuoteRequest::query()
            ->where('customer_id', $customer->id)
            ->with(self::RELATIONS)
            ->latest()
            ->get();
    }

    /**
     * List quote requests addressed to the authenticated store owner,
     * optionally filtered by status.
     */
    public function getMyStoreQuoteRequests(User $storeOwner, ?string $status = null): Collection
    {
        $store = $storeOwner->store;

        abort_unless($store, 404, 'Store profile not found.');

        return QuoteRequest::query()
            ->whereHas('storeProduct', fn ($query) => $query->where('store_id', $store->id))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with(self::RELATIONS)
            ->latest()
            ->get();
    }

    /**
     * Submit a new quote request for a store product.
     */
    public function createQuoteRequest(User $customer, array $data): QuoteRequest
    {
        $storeProduct = StoreProduct::query()->findOrFail($data['store_product_id']);

        abort_unless(
            $storeProduct->status === 'active',
            422,
            'This product is not currently available for quote requests.'
        );

        $quoteRequest = QuoteRequest::create([
            'customer_id' => $customer->id,
            'store_product_id' => $storeProduct->id,
            'quantity' => $data['quantity'],
            'customer_target_price' => $data['customer_target_price'] ?? null,
            'customer_notes' => $data['customer_notes'] ?? null,
            'status' => 'pending',
        ]);

        return $quoteRequest->load(self::RELATIONS);
    }

    /**
     * Store owner responds to a pending quote request with an offered price.
     */
    public function respondToQuote(User $storeOwner, QuoteRequest $quoteRequest, array $data): QuoteRequest
    {
        $this->ensureStoreOwnership($storeOwner, $quoteRequest);

        abort_unless(
            $quoteRequest->status === 'pending',
            422,
            'Only pending quote requests can be responded to.'
        );

        $quoteRequest->update([
            'offered_unit_price' => $data['offered_unit_price'],
            'total_price' => round($data['offered_unit_price'] * $quoteRequest->quantity, 2),
            'store_notes' => $data['store_notes'] ?? null,
            'status' => 'responded',
            'responded_at' => now(),
        ]);

        return $quoteRequest->load(self::RELATIONS);
    }

    /**
     * Customer accepts a store's offer.
     *
     * NOTE: acceptance only records the customer's intent on the quote request
     * itself. The current schema has no link between quote_requests and
     * cart_items/order_items, so converting an accepted quote into an actual
     * cart item or order at the negotiated price is not yet implemented here.
     */
    public function acceptQuote(User $customer, QuoteRequest $quoteRequest): QuoteRequest
    {
        $this->ensureCustomerOwnership($customer, $quoteRequest);

        abort_unless(
            $quoteRequest->status === 'responded',
            422,
            'Only a responded quote request can be accepted.'
        );

        $quoteRequest->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return $quoteRequest->load(self::RELATIONS);
    }

    /**
     * Customer rejects or cancels a quote request (before or after a store response).
     */
    public function rejectQuote(User $customer, QuoteRequest $quoteRequest): QuoteRequest
    {
        $this->ensureCustomerOwnership($customer, $quoteRequest);

        abort_unless(
            in_array($quoteRequest->status, ['pending', 'responded'], true),
            422,
            'This quote request can no longer be rejected.'
        );

        $quoteRequest->update(['status' => 'rejected']);

        return $quoteRequest->load(self::RELATIONS);
    }

    private function ensureCustomerOwnership(User $customer, QuoteRequest $quoteRequest): void
    {
        abort_unless(
            $quoteRequest->customer_id === $customer->id,
            403,
            'You are not allowed to manage this quote request.'
        );
    }

    private function ensureStoreOwnership(User $storeOwner, QuoteRequest $quoteRequest): void
    {
        $quoteRequest->loadMissing('storeProduct.store');

        abort_unless(
            $quoteRequest->storeProduct->store->user_id === $storeOwner->id,
            403,
            'You are not allowed to respond to this quote request.'
        );
    }
}
