<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreController extends Controller
{
    public function __construct(
        protected StoreService $storeService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $stores = $this->storeService->listStores();
        return StoreResource::collection($stores);
    }

    public function store(StoreStoreRequest $request): JsonResponse
    {
        $store = $this->storeService->createStore($request->validated());
        return (new StoreResource($store))
            ->response()
            ->setStatusCode(201);
    }

    public function show($id): StoreResource
    {
        $store = Store::with('user')->findOrFail($id);
        return new StoreResource($store);
    }

    public function update(UpdateStoreRequest $request, $id): StoreResource
    {
        $store = Store::findOrFail($id);
        $updatedStore = $this->storeService->updateStore($store, $request->validated());
        return new StoreResource($updatedStore);
    }

    public function destroy($id): JsonResponse
    {
        $store = Store::findOrFail($id);
        $this->storeService->deleteStore($store);
        return response()->json(['message' => 'Store deleted successfully']);
    }
}