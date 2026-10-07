<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get the authenticated user's cart, creating an empty one if needed.
     */
    public function getOrCreateCart(User $user): Cart
    {
        $cart = $user->cart()->firstOrCreate([]);

        return $cart->load(['items.storeProduct.masterProduct', 'items.storeProduct.store']);
    }

    /**
     * Add a product to the cart, or increase its quantity if already present.
     */
    public function addItem(User $user, array $data): Cart
    {
        return DB::transaction(function () use ($user, $data) {
            $cart = $user->cart()->firstOrCreate([]);

            $storeProduct = StoreProduct::query()
                ->lockForUpdate()
                ->findOrFail($data['store_product_id']);

            $existingItem = $cart->items()
                ->where('store_product_id', $storeProduct->id)
                ->first();

            $newQuantity = $data['quantity'] + ($existingItem?->quantity ?? 0);

            $this->assertPurchasable($storeProduct, $newQuantity);

            if ($existingItem) {
                $existingItem->update(['quantity' => $newQuantity]);
            } else {
                $cart->items()->create([
                    'store_product_id' => $storeProduct->id,
                    'quantity' => $newQuantity,
                ]);
            }

            return $cart->load(['items.storeProduct.masterProduct', 'items.storeProduct.store']);
        });
    }

    /**
     * Update the quantity of an existing cart item.
     */
    public function updateItemQuantity(User $user, CartItem $item, int $quantity): Cart
    {
        $this->ensureOwnership($user, $item);

        return DB::transaction(function () use ($user, $item, $quantity) {
            $storeProduct = StoreProduct::query()
                ->lockForUpdate()
                ->findOrFail($item->store_product_id);

            $this->assertPurchasable($storeProduct, $quantity);

            $item->update(['quantity' => $quantity]);

            return $this->getOrCreateCart($user);
        });
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(User $user, CartItem $item): Cart
    {
        $this->ensureOwnership($user, $item);

        $item->delete();

        return $this->getOrCreateCart($user);
    }

    /**
     * Empty the cart entirely (used after a successful checkout, or on demand).
     */
    public function clearCart(User $user): void
    {
        $cart = $user->cart;

        $cart?->items()->delete();
    }

    private function assertPurchasable(StoreProduct $storeProduct, int $quantity): void
    {
        abort_unless(
            $storeProduct->status === 'active',
            422,
            'This product is not currently available for purchase.'
        );

        abort_if(
            $quantity < $storeProduct->min_order_qty,
            422,
            "The minimum order quantity for this product is {$storeProduct->min_order_qty}."
        );

        abort_if(
            $quantity > $storeProduct->stock_quantity,
            422,
            "Only {$storeProduct->stock_quantity} unit(s) of this product are currently in stock."
        );
    }

    private function ensureOwnership(User $user, CartItem $item): void
    {
        abort_unless(
            $item->cart->user_id === $user->id,
            403,
            'You are not allowed to manage this cart item.'
        );
    }
}
