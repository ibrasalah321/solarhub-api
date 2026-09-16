<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGovernorateRequest;
use App\Models\Governorate;
use Illuminate\Http\JsonResponse;

class GovernorateController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Governorate::all());
    }

    public function store(StoreGovernorateRequest $request): JsonResponse
    {
        $governorate = Governorate::create($request->validated());
        return response()->json($governorate, 201);
    }

    public function show($id): JsonResponse
    {
        $governorate = Governorate::findOrFail($id);
        return response()->json($governorate);
    }

    public function update(StoreGovernorateRequest $request, $id): JsonResponse
    {
        $governorate = Governorate::findOrFail($id);
        $governorate->update($request->validated());
        return response()->json($governorate);
    }

    public function destroy($id): JsonResponse
    {
        Governorate::findOrFail($id)->delete();
        return response()->json(['message' => 'Governorate deleted successfully']);
    }
}