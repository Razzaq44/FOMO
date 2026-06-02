<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // User profile routes
    Route::get('/profile', function (Request $request) {
        return $request->user();
    });

    // Logout route
    Route::post('/logout', [AuthController::class, 'logout']);

    // Cart routes
    Route::get('/cart', [CartController::class, 'cartList']);
    Route::post('/cart', [CartController::class, 'addToCart']);
    Route::delete('/cart/{productId}', [CartController::class, 'removeFromCart']);
});
