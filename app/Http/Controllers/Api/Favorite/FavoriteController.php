<?php

namespace App\Http\Controllers\Api\Favorite;

use App\Http\Controllers\Controller;
use App\Http\Requests\Favorite\StoreFavoriteRequest;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Services\Favorite\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    public function __construct(
        protected FavoriteService $favoriteService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $favorites = $this->favoriteService->getUserFavorites(
            $request->user(),
            $request->integer('per_page', 15)
        );

        return FavoriteResource::collection($favorites);
    }

    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        $favorite = $this->favoriteService->addFavorite(
            $request->user(),
            $request->validated('store_product_id')
        );

        return (new FavoriteResource($favorite->load('storeProduct')))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, int $storeProductId): JsonResponse
    {
        $deleted = $this->favoriteService->removeFavorite($request->user(), $storeProductId);

        if (!$deleted) {
            return response()->json(['message' => 'العنصر غير موجود في قائمة المفضلة.'], 404);
        }

        return response()->json(['message' => 'تمت إزالة المنتج من المفضلة بنجاح.']);
    }

    public function toggle(Request $request, int $storeProductId): JsonResponse
    {
        $result = $this->favoriteService->toggleFavorite($request->user(), $storeProductId);

        return response()->json($result);
    }
}