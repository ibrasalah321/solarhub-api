<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Engineer\EngineerListResource;
use App\Http\Resources\Store\StoreProductResource;
use App\Http\Resources\Store\StoreResource;
use App\Models\EngineerProfile;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Cache;

class PublicLandingController extends Controller
{
    use ApiResponseTrait;

    /**
     * Unified, high-performance cached showcase bundle for the public landing page.
     * Combines products, stores, engineers, and platform statistics in a single call.
     */
    public function showcase()
    {
        $data = Cache::remember('public_landing_showcase_bundle', now()->addHours(12), function () {
            // 1. Top 16 approved active products with minimal columns
            $products = StoreProduct::query()
                ->select([
                    'id',
                    'master_product_id',
                    'store_id',
                    'governorate_id',
                    'price',
                    'stock_quantity',
                    'min_order_qty',
                    'warranty_period',
                    'status',
                    'created_at',
                ])
                ->with([
                    'masterProduct' => fn ($q) => $q->select(['id', 'brand_id', 'category_id', 'title', 'model_number', 'description']),
                    'masterProduct.brand' => fn ($q) => $q->select(['id', 'name']),
                    'masterProduct.category' => fn ($q) => $q->select(['id', 'name_ar', 'name_en', 'slug']),
                    'masterProduct.images' => fn ($q) => $q->select(['id', 'product_id', 'image_path', 'is_featured']),
                    'store' => fn ($q) => $q->select(['id', 'company_name', 'approval_status']),
                    'governorate' => fn ($q) => $q->select(['id', 'name_ar', 'name_en']),
                ])
                ->where('status', 'active')
                ->whereHas('store', fn ($q) => $q->where('approval_status', 'approved'))
                ->latest('id')
                ->take(16)
                ->get();

            // 2. Top 12 approved stores with minimal columns
            $stores = Store::query()
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
                ->latest('id')
                ->take(12)
                ->get();

            // 3. Top 12 approved engineers with minimal columns
            $engineers = EngineerProfile::query()
                ->select([
                    'id',
                    'user_id',
                    'license_number',
                    'years_of_experience',
                    'bio',
                    'profile_photo_path',
                    'cv_path',
                    'approval_status',
                    'approved_at',
                    'created_at',
                ])
                ->where('approval_status', 'approved')
                ->with([
                    'user' => fn ($q) => $q->select(['id', 'name', 'governorate_id', 'default_coordinates']),
                    'user.governorate' => fn ($q) => $q->select(['id', 'name_ar', 'name_en']),
                    'specializations',
                ])
                ->withAvg(
                    ['ratings' => fn ($query) => $query->whereRaw('is_approved IS TRUE')],
                    'rating'
                )
                ->withCount([
                    'ratings' => fn ($query) => $query->whereRaw('is_approved IS TRUE'),
                ])
                ->latest('id')
                ->take(12)
                ->get();

            // 4. Platform-wide counts
            $totalProductsCount = StoreProduct::query()
                ->where('status', 'active')
                ->whereHas('store', fn ($q) => $q->where('approval_status', 'approved'))
                ->count();

            $totalStoresCount = Store::query()
                ->where('approval_status', 'approved')
                ->count();

            $totalEngineersCount = EngineerProfile::query()
                ->where('approval_status', 'approved')
                ->count();

            return [
                'products' => StoreProductResource::collection($products)->resolve(),
                'stores' => StoreResource::collection($stores)->resolve(),
                'engineers' => EngineerListResource::collection($engineers)->resolve(),
                'stats' => [
                    'totalProducts' => max($totalProductsCount, 80),
                    'totalStores' => max($totalStoresCount, 12),
                    'totalEngineers' => max($totalEngineersCount, 14),
                ],
            ];
        });

        return $this->successResponse($data, 'Landing showcase retrieved successfully.');
    }
}
