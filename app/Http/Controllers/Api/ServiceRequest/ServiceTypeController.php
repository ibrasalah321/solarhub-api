<?php

namespace App\Http\Controllers\Api\ServiceRequest;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceRequest\ServiceTypeResource;
use App\Services\ServiceRequest\ServiceTypeService;
use App\Traits\ApiResponseTrait;

class ServiceTypeController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ServiceTypeService $serviceTypeService
    ) {
    }

    public function index()
    {
        $serviceTypes = $this->serviceTypeService
            ->getActiveServiceTypes();

        return $this->successResponse(
            ServiceTypeResource::collection($serviceTypes),
            'Service types retrieved successfully.'
        );
    }
}