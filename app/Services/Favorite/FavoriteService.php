<?php

namespace App\Services\Favorite;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class FavoriteService
{
    public function getUserFavorites(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Favorite::with(['storeProduct.store', 'storeProduct.masterProduct.images'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function addFavorite(User $user, int $storeProductId): Favorite
    {
        return DB::transaction(function () use ($user, $storeProductId) {
            return Favorite::firstOrCreate([
                'user_id' => $user->id,
                'store_product_id' => $storeProductId,
            ]);
        });
    }

    public function removeFavorite(User $user, int $storeProductId): bool
    {
        return (bool) Favorite::where('user_id', $user->id)
            ->where('store_product_id', $storeProductId)
            ->delete();
    }

    public function toggleFavorite(User $user, int $storeProductId): array
    {
        return DB::transaction(function () use ($user, $storeProductId) {
            $favorite = Favorite::where('user_id', $user->id)
                ->where('store_product_id', $storeProductId)
                ->first();

            if ($favorite) {
                $favorite->delete();
                return ['status' => 'removed', 'message' => 'تم حذف المنتج من المفضلة'];
            }

            $created = Favorite::create([
                'user_id' => $user->id,
                'store_product_id' => $storeProductId,
            ]);

            return ['status' => 'added', 'message' => 'تمت إضافة المنتج إلى المفضلة', 'data' => $created];
        });
    }
}