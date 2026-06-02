<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    use ApiResponse;

    /**
     * List all items in the authenticated user's cart.
     */
    public function cartList(): JsonResponse
    {
        $cart = Cart::with('items.product')->where('user_id', Auth::id())->first();

        $result = [
            'cart_id' => $cart ? $cart->id : null,
            'items' => $cart ? $cart->items : []
        ];

        return $this->successResponse($result, 'Cart retrieved successfully');
    }

    /**
     * Add a product to the cart or update quantity if it already exists.
     */
    public function addToCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $request->quantity;
                $cartItem->save();
            } else {
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity
                ]);
            }

            DB::commit();

            return $this->createdResponse($cartItem->load('product'), 'Product successfully added to cart');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to add product to cart', 500, $e->getMessage());
        }
    }

    /**
     * Remove a specific product from the cart.
     */
    public function removeFromCart($productId): JsonResponse
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return $this->notFoundResponse('Cart not found');
        }

        $deleted = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->delete();

        if (!$deleted) {
            return $this->notFoundResponse('Product not found in cart');
        }

        return $this->successResponse(null, 'Product successfully removed from cart');
    }

    /**
     * Clear all items from the cart.
     */
    public function clearCart(): JsonResponse
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return $this->notFoundResponse('Cart not found');
        }

        CartItem::where('cart_id', $cart->id)->delete();

        return $this->successResponse(null, 'Cart cleared successfully');
    }

    /**
     * Update the quantity of a specific product in the cart.
     * 
     * @param int $productId The ID of the product to update
     */
    public function updateCartItem(Request $request, int $productId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        if (!$user) {
            return $this->unauthorizedResponse();
        }

        $cart = $user->cart()->first();

        if (!$cart) {
            return $this->notFoundResponse('Cart not found');
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        if (!$cartItem) {
            return $this->notFoundResponse('Product not found in cart');
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return $this->successResponse(null, 'Cart updated successfully');
    }
}
