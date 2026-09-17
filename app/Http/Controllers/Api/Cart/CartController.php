<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use App\Services\Cart\CartService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly CartService $cartService
    ) {
    }

    /**
     * Display the authenticated user's cart.
     */
    public function show(Request $request)
    {
        $cart = $this->cartService->getOrCreateCart($request->user());

        return $this->successResponse(
            new CartResource($cart),
            'Cart retrieved successfully.'
        );
    }

    /**
     * Empty the authenticated user's cart.
     */
    public function destroy(Request $request)
    {
        $this->cartService->clearCart($request->user());

        return $this->successResponse(
            null,
            'Cart cleared successfully.'
        );
    }
}
