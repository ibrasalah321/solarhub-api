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
     * Caches the entire pool of active records, and returns a randomized selection
     * on every request to ensure diverse visibility across all products, stores, and engineers.
     */
    public function showcase()
    {
        // 1. Cache the entire active approved products pool (6 hours)
        $productsPool = \App\Support\PublicCache::remember('all_public_landing_products_pool', function () {
            return StoreProduct::query()
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
                ->get();
        });

        // 2. Cache the approved stores pool (6 hours)
        $storesPool = \App\Support\PublicCache::remember('all_public_landing_stores_pool', function () {
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
                    'storeProducts as store_products_count' => fn ($query) => $query->where('status', 'active'),
                ])
                ->get();
        });

        // 3. Cache the approved engineers pool (6 hours)
        $engineersPool = \App\Support\PublicCache::remember('all_public_landing_engineers_pool', function () {
            return EngineerProfile::query()
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
                ->get();
        });

        // 4. Randomize selection from total pools on each call
        $randomProducts = $productsPool->shuffle()->take(36);
        $randomStores = $storesPool->shuffle()->take(12);
        $randomEngineers = $engineersPool->shuffle()->take(12);

        $totalProductsCount = $productsPool->count();
        $totalStoresCount = $storesPool->count();
        $totalEngineersCount = $engineersPool->count();

        $data = [
            'products' => StoreProductResource::collection($randomProducts)->resolve(),
            'stores' => StoreResource::collection($randomStores)->resolve(),
            'engineers' => EngineerListResource::collection($randomEngineers)->resolve(),
            // أعداد حقيقية فقط: منتجات نشطة من متاجر معتمدة، متاجر معتمدة، مهندسون معتمدون
            'stats' => [
                'totalProducts' => $totalProductsCount,
                'totalStores' => $totalStoresCount,
                'totalEngineers' => $totalEngineersCount,
            ],
        ];

        return $this->successResponse($data, 'Landing showcase retrieved successfully.');
    }
}
