<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\FlashSale;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;

class CheckOutController extends Controller
{
    use ApiResponse;

    /**
     * Checkout the authenticated user's items and create an order.
     * 
     * @param Request $request
     */
    public function checkOut(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'checkout_type' => 'required|string|in:cart,buy_now',
            'product_id' => 'required_if:checkout_type,buy_now|exists:products,id',
            'flash_sale_id' => 'nullable|exists:flash_sales,id',
            'quantity' => 'required_if:checkout_type,buy_now|integer|min:1',
        ]);

        $checkoutType = $validate['checkout_type'];
        $productId = $validate['product_id'] ?? null;
        $flashSaleId = $validate['flash_sale_id'] ?? null;
        $quantity = $validate['quantity'] ?? null;

        $user = Auth::user();

        DB::beginTransaction();

        try {
            $orderItemData = [];
            $totalPrice = 0;

            if ($checkoutType === 'cart') {
                $cart = $user->cart()->first();

                if (!$cart) {
                    DB::rollBack();
                    return $this->notFoundResponse('Cart not found');
                }

                $items = CartItem::where('cart_id', $cart->id)->get();

                if ($items->isEmpty()) {
                    DB::rollBack();
                    return $this->errorResponse('Cart is empty', 400);
                }

                foreach ($items as $item) {
                    $flashSale = FlashSale::where('product_id', $item->product_id)
                        ->where('start_time', '<=', now())
                        ->where('end_time', '>=', now())
                        ->where('flash_sale_stock', '>', 0)
                        ->lockForUpdate()
                        ->first();

                    if ($flashSale) {
                        if ($flashSale->flash_sale_stock < $item->quantity) {
                            DB::rollBack();
                            return $this->errorResponse("Not enough flash sale stock for product {$flashSale->product->name}", 400);
                        }

                        $price = $flashSale->flash_sale_price;
                        $flashSale->flash_sale_stock -= $item->quantity;
                        $flashSale->save();
                    } else {
                        $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                        if (!$product) {
                            DB::rollBack();
                            return $this->errorResponse('Product not found', 404);
                        }

                        if ($product->stock < $item->quantity) {
                            DB::rollBack();
                            return $this->errorResponse("Not enough stock for product {$product->name}", 400);
                        }

                        $price = $product->price;
                        $product->stock -= $item->quantity;
                        $product->save();
                    }

                    $totalPrice += ($price * $item->quantity);
                    $orderItemData[] = [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $price,
                    ];
                }

                CartItem::whereIn('id', $items->pluck('id'))->delete();
            } else {
                $flashSale = FlashSale::where('product_id', $productId)
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->where('flash_sale_stock', '>', 0)
                    ->when($flashSaleId, function ($query, $flashSaleId) {
                        return $query->where('id', $flashSaleId);
                    })
                    ->lockForUpdate()
                    ->first();

                if ($flashSale) {
                    if ($flashSale->flash_sale_stock < $quantity) {
                        DB::rollBack();
                        return $this->errorResponse("Not enough flash sale stock for product {$flashSale->product->name}", 400);
                    }

                    $price = $flashSale->flash_sale_price;
                    $flashSale->flash_sale_stock -= $quantity;
                    $flashSale->save();
                } else {
                    $product = Product::where('id', $productId)->lockForUpdate()->first();

                    if (!$product) {
                        DB::rollBack();
                        return $this->errorResponse('Product not found', 404);
                    }

                    if ($product->stock < $quantity) {
                        DB::rollBack();
                        return $this->errorResponse("Not enough stock for product {$product->name}", 400);
                    }

                    $price = $product->price;
                    $product->stock -= $quantity;
                    $product->save();
                }

                $totalPrice += ($price * $quantity);
                $orderItemData[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            $order->items()->createMany($orderItemData);

            DB::commit();
            return $this->successResponse($order->load('items'), 'Checkout successful');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to checkout', 500, $e->getMessage());
        }
    }
}
