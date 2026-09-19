<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreGovernorateRequest;
use App\Http\Requests\Catalog\UpdateGovernorateRequest;
use App\Http\Resources\Catalog\GovernorateResource;
use App\Models\Governorate;
use App\Traits\ApiResponseTrait;

class GovernorateController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $governorates = Governorate::query()
            ->where('is_active', true)
            ->orderBy('name_ar')
            ->get();

        return $this->successResponse(
            GovernorateResource::collection($governorates),
            'Governorates retrieved successfully.'
        );
    }

    public function show(Governorate $governorate)
    {
        return $this->successResponse(
            new GovernorateResource($governorate),
            'Governorate retrieved successfully.'
        );
    }

    public function store(StoreGovernorateRequest $request)
    {
        $governorate = Governorate::create($request->validated());

        return $this->successResponse(
            new GovernorateResource($governorate),
            'Governorate created successfully.',
            201
        );
    }

    public function update(UpdateGovernorateRequest $request, Governorate $governorate)
    {
        $governorate->update($request->validated());

        return $this->successResponse(
            new GovernorateResource($governorate),
            'Governorate updated successfully.'
        );
    }

    public function destroy(Governorate $governorate)
    {
        abort_if(
            $governorate->users()->exists() || $governorate->storeProducts()->exists(),
            409,
            'This governorate is already in use and cannot be deleted.'
        );

        $governorate->delete();

        return $this->successResponse(null, 'Governorate deleted successfully.');
    }
}
