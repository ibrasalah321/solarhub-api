<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\QuoteRequest;
use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QuoteRequestService
{
    public function __construct(private readonly OrderService $orderService) {}

    private const RELATIONS = [
        'customer',
        'storeProduct.masterProduct.images',
        'storeProduct.masterProduct.brand',
        'storeProduct.masterProduct.category',
        'storeProduct.governorate',
        'storeProduct.store',
    ];

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
     * العميل يقبل عرض المورد: يُنشأ طلب شراء فوراً بالكمية والسعر المتفق عليهما، ويُربط بالعرض.
     * الطلب بعدها يمر بالمسار المعتاد (رفع إيصال الدفع ← اعتماد الإدارة ← الشحن).
     * العروض التي قُبلت قبل هذه الميزة (accepted بدون طلب) يمكن تحويلها بنفس المسار.
     *
     * @return array{0: QuoteRequest, 1: Order}
     */
    public function acceptQuote(User $customer, QuoteRequest $quoteRequest, array $delivery): array
    {
        $this->ensureCustomerOwnership($customer, $quoteRequest);

        return DB::transaction(function () use ($customer, $quoteRequest, $delivery) {
            // قفل العرض لمنع إنشاء طلبين من نفس العرض بنقرتين متزامنتين.
            $quote = QuoteRequest::query()->lockForUpdate()->findOrFail($quoteRequest->id);

            abort_unless(
                $quote->status === 'responded' || ($quote->status === 'accepted' && ! $quote->order_id),
                422,
                $quote->order_id
                    ? 'تم إنشاء طلب شراء من هذا العرض مسبقاً.'
                    : 'لا يمكن قبول هذا العرض إلا بعد رد المورد بالسعر.'
            );

            abort_if(
                $quote->offered_unit_price === null || (float) $quote->offered_unit_price <= 0,
                422,
                'العرض لا يحتوي سعراً صالحاً من المورد.'
            );

            $order = $this->orderService->createFromQuote($customer, $quote, $delivery);

            $quote->update([
                'status' => 'accepted',
                'accepted_at' => $quote->accepted_at ?? now(),
                'order_id' => $order->id,
            ]);

            // العميل والطلب متاحان مسبقاً؛ نحمّل فقط علاقات المنتج التي تعرضها الاستجابة.
            $quote->setRelation('customer', $customer);
            $quote->setRelation('order', $order);

            return [$quote->loadMissing([...self::RELATIONS, 'storeProduct.store.user']), $order];
        });
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
