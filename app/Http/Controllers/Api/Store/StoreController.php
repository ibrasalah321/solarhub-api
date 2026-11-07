<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Resources\Store\StoreResource;
use App\Models\Store;
use App\Services\Store\StoreService;
use App\Traits\ApiResponseTrait;

class StoreController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly StoreService $storeService
    ) {}

    public function index()
    {
        $perPage = (int) request()->input('per_page', 100);
        $page = (int) request()->input('page', 1);
        $cacheKey = "public_stores_page_{$page}_{$perPage}";

        $stores = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(12), function () use ($perPage) {
            return Store::query()
                ->select([
                    'id',
                    'user_id',
                    'company_name',
                    'bio',
                    'company_logo_path',
                    'whatsapp_number',
                    'store_type',
                    'approval_status',
                    'address_details',
                    'location_coordinates',
                    'created_at',
                ])
                ->where('approval_status', 'approved')
                ->with([
                    'user' => fn ($q) => $q->select(['id', 'name', 'governorate_id', 'default_coordinates']),
                    'user.governorate' => fn ($q) => $q->select(['id', 'name_ar', 'name_en']),
                ])
                ->withAvg('ratings', 'rating')
                ->withCount([
                    'ratings as approved_ratings_count' => fn ($query) => $query->whereRaw('is_approved IS TRUE'),
                ])
                ->paginate($perPage);
        });

        return $this->successResponse(
            StoreResource::collection($stores),
            'Stores retrieved successfully.'
        );
    }

    public function show(Store $store)
    {
        abort_unless($store->approval_status === 'approved', 404);

        $store->load('user.governorate')
            ->loadAvg('ratings', 'rating')
            ->loadCount([
                'ratings as approved_ratings_count' => fn ($query) => $query->whereRaw('is_approved IS TRUE'),
            ]);

        return $this->successResponse(
            new StoreResource($store),
            'Store retrieved successfully.'
        );
    }

    public function update(UpdateStoreRequest $request, Store $store)
    {
        $data = $request->validated();

        if ($request->hasFile('commercial_file')) {
            $data['commercial_file'] = $request->file('commercial_file');
        }

        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = $request->file('company_logo');
        }

        $store = $this->storeService->updateMyStore(
            $request->user(),
            $store,
            $data
        );

        return $this->successResponse(
            new StoreResource($store),
            'Store updated successfully.'
        );
    }
}
