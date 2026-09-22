<?php

namespace App\Http\Controllers\Auth\Engineer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Engineer\StoreEngineerOnboardingRequest;
use App\Http\Resources\Auth\Engineer\EngineerOnboardingResource;
use App\Services\Auth\Engineer\EngineerOnboardingService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
class EngineerOnboardingController extends Controller
{
    use ApiResponseTrait;
    public function __construct(private readonly EngineerOnboardingService $engineerOnboardingService)
    {
        
    }

    public function store(StoreEngineerOnboardingRequest $engineerOnboardingRequest)
    {
        // 1. validated data
        $data = $engineerOnboardingRequest->validated();

        // 2. استدعاء service وتمرير user + data
        $engineer = $this->engineerOnboardingService->store($engineerOnboardingRequest->user(),$data);

        // 3. إرجاع EngineerOnboardingResource
        return $this->successResponse(new EngineerOnboardingResource($engineer) , '');
    }
    public function status(Request $request){
        $engineer = $this->engineerOnboardingService->status(
            $request->user()
        );
        if(!$engineer){
            return $this->successResponse(null,'Engineer onboarding has not been submitted yet.');
        }
        return $this->successResponse(new EngineerOnboardingResource($engineer) , 'Engineer onboarding status retrieved successfully.');

    }
}
