<?php

namespace App\Http\Controllers\Api\Enginner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Engineer\UpdateEngineerProfileRequest;
use App\Http\Resources\Engineer\EngineerListResource;
use App\Http\Resources\Engineer\EngineerProfileResource;
use App\Services\Engineer\EngineerProfileService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Http\Requests\Engineer\EngineerIndexRequest;

class EngineerProfileController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly EngineerProfileService $engineerProfileService
    ) {
    }

    /**
     * Display approved engineers.
     */
    public function index(EngineerIndexRequest $request)
    {
        $engineers = $this->engineerProfileService
            ->getApprovedEngineers($request->validated());

        return $this->successResponse(
            EngineerListResource::collection($engineers),
            'Engineers retrieved successfully.'
        );
    }

    /**
     * Display one approved engineer.
     */
    public function show(int $engineer)
    {
        $engineerProfile = $this->engineerProfileService
            ->getApprovedEngineer($engineer);

        return $this->successResponse(
            new EngineerProfileResource($engineerProfile),
            'Engineer retrieved successfully.'
        );
    }

    /**
     * Display authenticated engineer profile.
     */
    public function myProfile(Request $request)
    {
        $engineer = $this->engineerProfileService
            ->getMyProfile($request->user());

        if (!$engineer) {
            return $this->errorResponse(
                'Engineer profile not found.',
                null,
                404
            );
        }

        return $this->successResponse(
            new EngineerProfileResource($engineer),
            'Engineer profile retrieved successfully.'
        );
    }

    /**
     * Update authenticated engineer profile.
     */
    public function update(UpdateEngineerProfileRequest $request)
    {
        $engineer = $this->engineerProfileService
            ->getMyProfile($request->user());

        if (!$engineer) {
            return $this->errorResponse(
                'Engineer profile not found.',
                null,
                404
            );
        }

        $engineer = $this->engineerProfileService->updateProfile(
            $engineer,
            $request->validated()
        );

        return $this->successResponse(
            new EngineerProfileResource($engineer),
            'Engineer profile updated successfully.'
        );
    }
}