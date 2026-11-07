<?php

namespace App\Services\Store;

use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class StoreProductService
{
    /**
     * Public storefront browsing of active listings, with basic filters.
     */
    public function browse(array $filters): LengthAwarePaginator
    {
        $page = (int) request()->input('page', 1);
        $perPage = (int) ($filters['per_page'] ?? request()->input('per_page', 100));
        $cacheKey = 'public_store_products_' . md5(json_encode($filters) . "_{$page}_{$perPage}");

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(12), function () use ($filters, $perPage) {
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
                ->whereHas('store', function (Builder $storeQuery) {
                    $storeQuery->where('approval_status', 'approved');
                })
                ->when(
                    $filters['governorate_id'] ?? null,
                    fn (Builder $query, $id) => $query->where('governorate_id', $id)
                )
                ->when(
                    $filters['category_id'] ?? null,
                    fn (Builder $query, $id) => $query->whereHas(
                        'masterProduct',
                        fn (Builder $productQuery) => $productQuery->where('category_id', $id)
                    )
                )
                ->when(
                    $filters['store_id'] ?? null,
                    fn (Builder $query, $id) => $query->where('store_id', $id)
                )
                ->when(
                    $filters['random'] ?? request()->boolean('random'),
                    fn (Builder $query) => $query->inRandomOrder()
                )
                ->paginate($perPage);
        });
    }

    /**
     * List the authenticated store owner's own listings.
     */
    public function getMyListings(User $storeOwner): LengthAwarePaginator
    {
        $store = $storeOwner->store;

        abort_unless($store, 404, 'Store profile not found.');

        return $store->storeProducts()
            ->with([
                'masterProduct.brand',
                'masterProduct.category',
                'masterProduct.images',
                'governorate',
            ])
            ->latest()
            ->paginate((int) request()->input('per_page', 100));
    }

    /**
     * Create a new listing for the authenticated store owner.
     */
    public function createListing(User $storeOwner, array $data): StoreProduct
    {
        $store = $storeOwner->store;

        abort_unless($store, 404, 'Store profile not found.');

        abort_unless(
            $store->approval_status === 'approved',
            403,
            'Your store must be approved before you can list products.'
        );

        $this->ensureUnique($store->id, $data['master_product_id'], $data['governorate_id'] ?? null);

        return $store->storeProducts()->create([
            'master_product_id' => $data['master_product_id'],
            'governorate_id' => $data['governorate_id'] ?? null,
            'price' => $data['price'] ?? null,
            'stock_quantity' => $data['stock_quantity'],
            'min_order_qty' => $data['min_order_qty'],
            'warranty_period' => $data['warranty_period'] ?? null,
            'status' => (int) $data['stock_quantity'] > 0 ? 'active' : 'out_of_stock',
        ])->load([
            'masterProduct.brand',
            'masterProduct.category',
            'masterProduct.images',
            'governorate',
        ]);
    }

    /**
     * Update an existing listing owned by the authenticated store owner.
     */
    public function updateListing(User $storeOwner, StoreProduct $storeProduct, array $data): StoreProduct
    {
        $this->ensureOwnership($storeOwner, $storeProduct);

        // Automatic stock-driven status derivation, unless the caller explicitly
        // set 'inactive' (a deliberate pause that stock changes shouldn't override).
        if (array_key_exists('stock_quantity', $data) && ! isset($data['status'])) {
            $data['status'] = (int) $data['stock_quantity'] > 0 ? 'active' : 'out_of_stock';
        }

        $storeProduct->update($data);

        return $storeProduct->load([
            'masterProduct.brand',
            'masterProduct.category',
            'masterProduct.images',
            'governorate',
        ]);
    }

    /**
     * Delete a listing owned by the authenticated store owner.
     */
    public function deleteListing(User $storeOwner, StoreProduct $storeProduct): void
    {
        $this->ensureOwnership($storeOwner, $storeProduct);

        abort_if(
            $storeProduct->orderItems()->exists(),
            409,
            'This product has order history and cannot be deleted. Consider marking it as inactive instead.'
        );

        $storeProduct->delete();
    }

    private function ensureUnique(int $storeId, int $masterProductId, ?int $governorateId): void
    {
        $exists = StoreProduct::query()
            ->where('store_id', $storeId)
            ->where('master_product_id', $masterProductId)
            ->where('governorate_id', $governorateId)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'master_product_id' => 'You already have a listing for this product in the selected governorate.',
            ]);
        }
    }

    private function ensureOwnership(User $storeOwner, StoreProduct $storeProduct): void
    {
        abort_unless(
            $storeProduct->store->user_id === $storeOwner->id,
            403,
            'You are not allowed to manage this product listing.'
        );
    }
}
