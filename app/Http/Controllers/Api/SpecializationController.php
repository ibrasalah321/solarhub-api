<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SpecializationResource;
use App\Services\SpecializationService;
use App\Traits\ApiResponseTrait;

class SpecializationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly SpecializationService $specializationService
    ) {
    }

    public function index()
    {
        $specializations = $this->specializationService
            ->getActiveSpecializations();

        return $this->successResponse(
            SpecializationResource::collection($specializations),
            'Specializations retrieved successfully.'
        );
    }
}