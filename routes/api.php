<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\CheckOutController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public product routes
Route::get('/products', [ProductController::class, 'listProducts']);
Route::get('/products/{id}', [ProductController::class, 'productDetails']);

Route::middleware('auth:sanctum')->group(function () {
    // User profile routes
    Route::get('/profile', [AuthController::class, 'userProfile']);

    // Logout route
    Route::post('/logout', [AuthController::class, 'logout']);

    // Cart routes
    Route::get('/cart', [CartController::class, 'cartList']);
    Route::post('/cart', [CartController::class, 'addToCart']);
    Route::delete('/cart/{productId}', [CartController::class, 'removeFromCart']);
    Route::put('/cart/{productId}', [CartController::class, 'updateCartItem']);
    Route::delete('/cart', [CartController::class, 'clearCart']);

    // Checkout routes
    Route::post('/checkout', [CheckOutController::class, 'checkOut']);

    // Order routes
    Route::get('/orders', [OrderController::class, 'orderList']);
    Route::get('/orders/{id}', [OrderController::class, 'orderDetails']);
    Route::patch('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::put('/orders/{id}', [OrderController::class, 'updateOrderStatus']);

    // Product routes for authenticated users (Admin)
    Route::post('/products', [ProductController::class, 'createProduct']);
    Route::put('/products/{id}', [ProductController::class, 'updateProduct']);
    Route::delete('/products/{id}', [ProductController::class, 'deleteProduct']);
});
