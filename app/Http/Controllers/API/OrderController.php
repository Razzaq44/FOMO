<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    use ApiResponse;

    /**
     * List all orders for the authenticated user, with optional status filter.
     * 
     * @queryParam status string Optional order status filter (pending, processing, shipped, delivered, cancelled)
     */
    public function orderList(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'status' => 'nullable|string|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $status = $validate['status'] ?? null;

        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->with('items')
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        if ($orders->isEmpty()) {
            return $this->notFoundResponse('No orders found');
        }

        return $this->successResponse($orders, 'Orders retrieved successfully');
    }

    /**
     * Get details of a specific order by ID.
     * 
     * @param int $id Order ID
     */
    public function orderDetails($id): JsonResponse
    {
        $user = Auth::user();
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->with('items')
            ->first();

        if (!$order) {
            return $this->notFoundResponse('Order not found');
        }

        return $this->successResponse($order, 'Order details retrieved successfully');
    }

    /**
     * Cancel an order.
     * 
     * @param int $id Order ID
     */
    public function cancelOrder($id): JsonResponse
    {
        $user = Auth::user();
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return $this->notFoundResponse('Order not found or cannot be cancelled');
        }

        $order->status = 'cancelled';
        $order->save();

        return $this->successResponse($order, 'Order cancelled successfully');
    }

    /**
     * Update the status of an order (for admin use, not yet implemented RBAC).
     * 
     * @param int $id Order ID
     */
    public function updateOrderStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $status = $request->status;

        $order = Order::find($id);

        if (!$order) {
            return $this->notFoundResponse('Order not found');
        }

        $order->status = $status;
        $order->save();

        return $this->successResponse($order, 'Order status updated successfully');
    }
}
