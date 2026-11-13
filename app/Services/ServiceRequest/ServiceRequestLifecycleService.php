<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Notification\DomainNotificationDispatcher;
use Illuminate\Support\Facades\DB;

/**
 * انتقالات طلب الخدمة بعد قبول العرض.
 *
 *   awarded ──start──▶ in_progress ──mark-done──▶ (engineer_completed_at) ──confirm-completion──▶ completed
 *
 * الصلاحية (من يحق له) تُفحص في ServiceRequestPolicy عبر middleware can:.
 * هنا نفحص الحالة (هل الانتقال مسموح الآن) داخل معاملة مع قفل الصف لمنع السباق.
 */
class ServiceRequestLifecycleService
{
    public function __construct(
        private readonly DomainNotificationDispatcher $notifications
    ) {}

    public function start(User $engineer, ServiceRequest $serviceRequest): ServiceRequest
    {
        return DB::transaction(function () use ($engineer, $serviceRequest) {
            $locked = $this->lock($serviceRequest);

            abort_unless(
                $locked->status === 'awarded',
                422,
                'لا يمكن بدء التنفيذ إلا لطلب تم قبول عرض عليه ولم يبدأ بعد.'
            );

            $locked->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            $this->notifications->serviceStarted($engineer, $locked);

            return $this->fresh($locked);
        });
    }

    public function markDone(User $engineer, ServiceRequest $serviceRequest): ServiceRequest
    {
        return DB::transaction(function () use ($engineer, $serviceRequest) {
            $locked = $this->lock($serviceRequest);

            abort_unless(
                $locked->status === 'in_progress',
                422,
                'يمكن الإبلاغ بإنهاء العمل فقط لطلب قيد التنفيذ.'
            );

            abort_if(
                $locked->engineer_completed_at !== null,
                422,
                'تم الإبلاغ بإنهاء العمل مسبقاً، بانتظار تأكيد العميل.'
            );

            $locked->update([
                'engineer_completed_at' => now(),
            ]);

            $this->notifications->serviceMarkedDone($engineer, $locked);

            return $this->fresh($locked);
        });
    }

    public function confirmCompletion(User $customer, ServiceRequest $serviceRequest): ServiceRequest
    {
        return DB::transaction(function () use ($customer, $serviceRequest) {
            $locked = $this->lock($serviceRequest);

            abort_unless(
                $locked->status === 'in_progress',
                422,
                'يمكن تأكيد الاكتمال فقط لطلب قيد التنفيذ.'
            );

            // لا يُغلق المشروع قبل أن يبلغ المهندس بتسليم العمل
            abort_if(
                $locked->engineer_completed_at === null,
                422,
                'لم يبلغ المهندس بإنهاء العمل بعد.'
            );

            $locked->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $this->notifications->serviceCompleted($customer, $locked);

            return $this->fresh($locked);
        });
    }

    private function lock(ServiceRequest $serviceRequest): ServiceRequest
    {
        return ServiceRequest::query()->whereKey($serviceRequest->id)->lockForUpdate()->firstOrFail();
    }

    private function fresh(ServiceRequest $serviceRequest): ServiceRequest
    {
        return $serviceRequest->fresh([
            'customer',
            'serviceType',
            'governorate',
            'offers.engineer.user',
            'rating',
        ]);
    }
}
