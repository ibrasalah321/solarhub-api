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
        return StoreProduct::query()
            ->with(['masterProduct.brand', 'masterProduct.category', 'store', 'governorate'])
            ->where('status', 'active')
            ->when($filters['governorate_id'] ?? null, fn (Builder $q, $id) => $q->where('governorate_id', $id))
            ->when($filters['category_id'] ?? null, fn (Builder $q, $id) => $q->whereHas(
                'masterProduct',
                fn (Builder $mp) => $mp->where('category_id', $id)
            ))
            ->when($filters['store_id'] ?? null, fn (Builder $q, $id) => $q->where('store_id', $id))
            ->paginate(20);
    }

    /**
     * List the authenticated store owner's own listings.
     */
    public function getMyListings(User $storeOwner): LengthAwarePaginator
    {
        $store = $storeOwner->store;

        abort_unless($store, 404, 'Store profile not found.');

        return $store->storeProducts()
            ->with(['masterProduct', 'governorate'])
            ->latest()
            ->paginate(20);
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
        ])->load(['masterProduct', 'governorate']);
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

        return $storeProduct->load(['masterProduct', 'governorate']);
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
