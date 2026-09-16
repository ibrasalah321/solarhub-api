<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoreRatingRequest;
use App\Models\StoreRating;
use Illuminate\Http\JsonResponse;

class StoreRatingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(StoreRating::with(['orderStore', 'customer', 'store'])->paginate(20));
    }

    public function store(StoreStoreRatingRequest $request): JsonResponse
    {
        $rating = StoreRating::create($request->validated());
        return response()->json($rating, 201);
    }

    public function show($id): JsonResponse
    {
        $rating = StoreRating::with(['orderStore', 'customer', 'store'])->findOrFail($id);
        return response()->json($rating);
    }

    public function update(StoreStoreRatingRequest $request, $id): JsonResponse
    {
        $rating = StoreRating::findOrFail($id);
        $rating->update($request->validated());
        return response()->json($rating);
    }

    public function destroy($id): JsonResponse
    {
        StoreRating::findOrFail($id)->delete();
        return response()->json(['message' => 'Store rating deleted successfully']);
    }
}