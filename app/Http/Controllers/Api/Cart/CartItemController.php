<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly CartService $cartService
    ) {
    }

    /**
     * Add a product to the authenticated user's cart.
     */
    public function store(StoreCartItemRequest $request)
    {
        $cart = $this->cartService->addItem(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new CartResource($cart),
            'Product added to cart successfully.',
            201
        );
    }

    /**
     * Update the quantity of a cart item.
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem)
    {
        $cart = $this->cartService->updateItemQuantity(
            $request->user(),
            $cartItem,
            $request->validated('quantity')
        );

        return $this->successResponse(
            new CartResource($cart),
            'Cart item updated successfully.'
        );
    }

    /**
     * Remove a product from the cart.
     */
    public function destroy(Request $request, CartItem $cartItem)
    {
        $cart = $this->cartService->removeItem(
            $request->user(),
            $cartItem
        );

        return $this->successResponse(
            new CartResource($cart),
            'Product removed from cart successfully.'
        );
    }
}
