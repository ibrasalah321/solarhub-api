<?php

namespace App\Services\Notification;

use App\Models\EngineerProfile;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderStore;
use App\Models\QuoteRequest;
use App\Models\ServiceRequest;
use App\Models\Store;
use App\Models\StorePayout;
use App\Models\User;

class DomainNotificationDispatcher
{
    public function __construct(
        private readonly NotificationService $notifications
    ) {}

    public function accountCreated(User $user, string $role): void
    {
        $this->notifications->sendAfterCommit(
            [$user],
            'account_created',
            [
                'user_name' => $user->name,
                'account_type' => $this->roleLabel($role),
            ],
            "account-created:{$user->id}",
            systemGenerated: true
        );

        if (in_array($role, ['engineer', 'supplier'], true)) {
            $this->professionalApplicationSubmitted(
                $user,
                $role
            );
        }
    }

    public function professionalApplicationSubmitted(
        User $user,
        string $role
    ): void {
        $this->notifications->sendAfterCommit(
            $this->admins(),
            'professional_application_submitted_admin',
            [
                'user_name' => $user->name,
                'account_type' => $this->roleLabel($role),
            ],
            "professional-application:{$role}:{$user->id}",
            $user,
            '/admin/applications'
        );
    }

    public function professionalDecision(
        User $admin,
        EngineerProfile|Store $application,
        bool $approved
    ): void {
        $isEngineer = $application instanceof EngineerProfile;
        $template = $isEngineer
            ? ($approved ? 'engineer_application_approved' : 'engineer_application_rejected')
            : ($approved ? 'supplier_application_approved' : 'supplier_application_rejected');

        $placeholders = $isEngineer
            ? ['user_name' => $application->user->name]
            : ['store_name' => $application->company_name];

        if (! $approved) {
            $placeholders['rejection_reason'] = $application->rejection_reason;
        }

        $this->notifications->sendAfterCommit(
            [$application->user],
            $template,
            $placeholders,
            "professional-decision:{$application->getTable()}:{$application->id}:".($approved ? 'approved' : 'rejected'),
            $admin,
            '/dashboard/profile'
        );
    }

    public function orderCreated(User $customer, Order $order): void
    {
        $this->notifications->sendAfterCommit(
            [$customer],
            'order_created_customer',
            [
                'order_number' => $order->order_number,
                'amount' => $order->total_amount,
            ],
            "order-created-customer:{$order->id}",
            systemGenerated: true,
            actionUrl: "/orders/{$order->id}"
        );

        foreach ($order->orderStores as $orderStore) {
            $this->notifications->sendAfterCommit(
                [$orderStore->store->user],
                'order_created_store',
                [
                    'store_name' => $orderStore->store->company_name,
                    'order_number' => $order->order_number,
                    'amount' => $orderStore->subtotal,
                ],
                "order-created-store:{$orderStore->id}",
                $customer,
                "/order-stores/{$orderStore->id}"
            );
        }
    }

    public function orderCancelled(User $customer, Order $order): void
    {
        foreach ($order->orderStores as $orderStore) {
            $this->notifications->sendAfterCommit(
                [$orderStore->store->user],
                'order_cancelled_store',
                [
                    'customer_name' => $customer->name,
                    'order_number' => $order->order_number,
                ],
                "order-cancelled:{$orderStore->id}",
                $customer,
                "/order-stores/{$orderStore->id}"
            );
        }
    }

    public function orderStatusChanged(
        User $storeOwner,
        OrderStore $orderStore
    ): void {
        $this->notifications->sendAfterCommit(
            [$orderStore->order->customer],
            'order_status_changed_customer',
            [
                'order_number' => $orderStore->order->order_number,
                'store_name' => $orderStore->store->company_name,
                'status' => $orderStore->status,
            ],
            "order-status:{$orderStore->id}:{$orderStore->status}",
            $storeOwner,
            "/orders/{$orderStore->order_id}"
        );
    }

    public function deliveryConfirmed(
        User $customer,
        OrderStore $orderStore
    ): void {
        $this->notifications->sendAfterCommit(
            [$orderStore->store->user],
            'delivery_confirmed_store',
            [
                'order_number' => $orderStore->order->order_number,
                'store_name' => $orderStore->store->company_name,
            ],
            "delivery-confirmed:{$orderStore->id}",
            $customer,
            "/order-stores/{$orderStore->id}"
        );
    }

    public function serviceRequestSubmitted(
        User $customer,
        ServiceRequest $serviceRequest
    ): void {
        $engineers = User::role('engineer')
            ->whereHas('engineerProfile', fn ($query) => $query->where('approval_status', 'approved'))
            ->get();

        $this->notifications->sendAfterCommit(
            $engineers,
            'service_request_submitted_engineer',
            [
                'service_request_id' => $serviceRequest->id,
                'customer_name' => $customer->name,
                'service_type' => $serviceRequest->serviceType->name,
            ],
            "service-request-submitted:{$serviceRequest->id}",
            $customer,
            "/service-requests/{$serviceRequest->id}"
        );
    }

    public function offerSubmitted(User $engineer, Offer $offer): void
    {
        $this->notifications->sendAfterCommit(
            [$offer->serviceRequest->customer],
            'offer_submitted_customer',
            [
                'engineer_name' => $engineer->name,
                'service_request_id' => $offer->service_request_id,
                'amount' => $offer->proposed_cost,
            ],
            "offer-submitted:{$offer->id}",
            $engineer,
            "/service-requests/{$offer->service_request_id}/offers"
        );
    }

    public function offerAccepted(User $customer, Offer $acceptedOffer): void
    {
        $offers = Offer::query()
            ->with('engineer.user')
            ->where('service_request_id', $acceptedOffer->service_request_id)
            ->get();

        foreach ($offers as $offer) {
            $accepted = $offer->id === $acceptedOffer->id;

            $this->notifications->sendAfterCommit(
                [$offer->engineer->user],
                $accepted ? 'offer_accepted_engineer' : 'offer_rejected_engineer',
                [
                    'offer_id' => $offer->id,
                    'service_request_id' => $offer->service_request_id,
                ],
                "offer-decision:{$offer->id}:".($accepted ? 'accepted' : 'rejected'),
                $customer,
                "/offers/{$offer->id}"
            );
        }
    }

    public function quoteRequested(User $customer, QuoteRequest $quote): void
    {
        $this->notifications->sendAfterCommit(
            [$quote->storeProduct->store->user],
            'quote_requested_store',
            [
                'customer_name' => $customer->name,
                'product_name' => $quote->storeProduct->masterProduct->title,
                'quantity' => $quote->quantity,
            ],
            "quote-requested:{$quote->id}",
            $customer,
            "/quote-requests/{$quote->id}"
        );
    }

    public function quoteResponded(User $storeOwner, QuoteRequest $quote): void
    {
        $this->notifications->sendAfterCommit(
            [$quote->customer],
            'quote_responded_customer',
            [
                'store_name' => $quote->storeProduct->store->company_name,
                'quote_id' => $quote->id,
                'amount' => $quote->total_price,
            ],
            "quote-responded:{$quote->id}",
            $storeOwner,
            "/quote-requests/{$quote->id}"
        );
    }

    public function quoteDecision(
        User $customer,
        QuoteRequest $quote
    ): void {
        $this->notifications->sendAfterCommit(
            [$quote->storeProduct->store->user],
            $quote->status === 'accepted'
                ? 'quote_accepted_store'
                : 'quote_rejected_store',
            ['quote_id' => $quote->id],
            "quote-decision:{$quote->id}:{$quote->status}",
            $customer,
            "/quote-requests/{$quote->id}"
        );
    }

    public function paymentSubmitted(
        User $customer,
        OrderPayment $payment
    ): void {
        $this->notifications->sendAfterCommit(
            $this->admins(),
            'payment_submitted_admin',
            [
                'customer_name' => $customer->name,
                'order_number' => $payment->order->order_number,
                'amount' => $payment->amount,
            ],
            "payment-submitted:{$payment->id}",
            $customer,
            "/payments/{$payment->id}"
        );
    }

    public function paymentDecision(User $admin, OrderPayment $payment): void
    {
        $this->notifications->sendAfterCommit(
            [$payment->order->customer],
            $payment->status === 'verified'
                ? 'payment_verified_customer'
                : 'payment_rejected_customer',
            [
                'order_number' => $payment->order->order_number,
                'amount' => $payment->amount,
            ],
            "payment-decision:{$payment->id}:{$payment->status}",
            $admin,
            "/orders/{$payment->order_id}"
        );
    }

    public function payoutCreated(User $admin, StorePayout $payout): void
    {
        $this->sendPayout(
            $admin,
            $payout,
            'payout_created_store',
            "payout-created:{$payout->id}"
        );
    }

    public function payoutStatusChanged(User $admin, StorePayout $payout): void
    {
        $this->sendPayout(
            $admin,
            $payout,
            'payout_status_changed_store',
            "payout-status:{$payout->id}:{$payout->status}"
        );
    }

    private function sendPayout(
        User $admin,
        StorePayout $payout,
        string $template,
        string $eventKey
    ): void {
        $store = $payout->orderStore->store;

        $this->notifications->sendAfterCommit(
            [$store->user],
            $template,
            [
                'store_name' => $store->company_name,
                'order_number' => $payout->orderStore->order->order_number,
                'amount' => $payout->net_amount,
                'status' => $payout->status,
            ],
            $eventKey,
            $admin,
            "/store-payouts/{$payout->id}"
        );
    }

    private function admins()
    {
        return User::role('admin')->get();
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            'engineer' => 'مهندس',
            'supplier' => 'تاجر',
            default => 'عميل',
        };
    }
}
