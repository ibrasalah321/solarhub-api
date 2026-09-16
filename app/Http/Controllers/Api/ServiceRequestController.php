<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest\ServiceRequestIndexRequest;
use App\Http\Requests\ServiceRequest\StoreServiceRequestRequest;
use App\Http\Requests\ServiceRequest\UpdateServiceRequestRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;
use App\Services\ServiceRequestService;
use App\Traits\ApiResponseTrait;

class ServiceRequestController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ServiceRequestService $serviceRequestService
    ) {
    }

    public function myRequests(ServiceRequestIndexRequest $request)
    {
        $serviceRequests =
            $this->serviceRequestService->getCustomerRequests(
                $request->user(),
                $request->validated()
            );

        return $this->successResponse(
            ServiceRequestResource::collection($serviceRequests),
            'Service requests retrieved successfully.'
        );
    }

    public function openRequests(ServiceRequestIndexRequest $request)
    {
        $serviceRequests =
            $this->serviceRequestService->getOpenRequests(
                $request->validated()
            );

        return $this->successResponse(
            ServiceRequestResource::collection($serviceRequests),
            'Open service requests retrieved successfully.'
        );
    }

    public function store(StoreServiceRequestRequest $request)
    {
        $serviceRequest =
            $this->serviceRequestService->create(
                $request->user(),
                $request->validated()
            );

        return $this->successResponse(
            new ServiceRequestResource($serviceRequest),
            'Service request created successfully.',
            201
        );
    }

    public function update(UpdateServiceRequestRequest $request,ServiceRequest $serviceRequest) {
        $serviceRequest =
            $this->serviceRequestService->update(
                $request->user(),
                $serviceRequest,
                $request->validated()
            );

        return $this->successResponse(
            new ServiceRequestResource($serviceRequest),
            'Service request updated successfully.'
        );
    }

    public function cancel(ServiceRequestIndexRequest $request,ServiceRequest $serviceRequest) {
        $serviceRequest =
            $this->serviceRequestService->cancel(
                $request->user(),
                $serviceRequest
            );

        return $this->successResponse(
            new ServiceRequestResource($serviceRequest),
            'Service request cancelled successfully.'
        );
    }
}