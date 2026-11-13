<?php

namespace App\Http\Controllers\Api\ServiceRequest;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceRequest\ServiceRequestResource;
use App\Models\ServiceRequest;
use App\Services\ServiceRequest\ServiceRequestLifecycleService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

/**
 * انتقالات طلب الخدمة بعد قبول العرض. الصلاحية عبر ServiceRequestPolicy (middleware can:)
 * وفحص الحالة في ServiceRequestLifecycleService.
 */
class ServiceRequestLifecycleController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ServiceRequestLifecycleService $lifecycle
    ) {}

    public function start(Request $request, ServiceRequest $serviceRequest)
    {
        return $this->successResponse(
            new ServiceRequestResource($this->lifecycle->start($request->user(), $serviceRequest)),
            'تم بدء تنفيذ طلب الخدمة.'
        );
    }

    public function markDone(Request $request, ServiceRequest $serviceRequest)
    {
        return $this->successResponse(
            new ServiceRequestResource($this->lifecycle->markDone($request->user(), $serviceRequest)),
            'تم إبلاغ العميل بإنهاء العمل، بانتظار تأكيده.'
        );
    }

    public function confirmCompletion(Request $request, ServiceRequest $serviceRequest)
    {
        return $this->successResponse(
            new ServiceRequestResource($this->lifecycle->confirmCompletion($request->user(), $serviceRequest)),
            'تم تأكيد اكتمال المشروع. يمكنك الآن تقييم المهندس.'
        );
    }
}
