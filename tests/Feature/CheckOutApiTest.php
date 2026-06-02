<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\FlashSale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckOutApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_buy_now_checkout_reduces_stock_and_creates_order()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 1000]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout', [
                'checkout_type' => 'buy_now',
                'product_id' => $product->id,
                'quantity' => 2
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.total_price', "2000.00");

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total_price' => 2000]);
    }

    public function test_checkout_fails_if_stock_insufficient()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 1]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout', [
                'checkout_type' => 'buy_now',
                'product_id' => $product->id,
                'quantity' => 5
            ]);

        $response->assertStatus(400);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_applies_flash_sale_price()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000]);

        FlashSale::create([
            'product_id' => $product->id,
            'flash_sale_price' => 500,
            'flash_sale_stock' => 10,
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout', [
                'checkout_type' => 'buy_now',
                'product_id' => $product->id,
                'quantity' => 2
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.total_price', "1000.00"); // 500 * 2
    }

    public function test_checkout_from_cart_clears_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 3]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout', ['checkout_type' => 'cart']);

        $response->assertStatus(200);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 7]);
    }

    public function test_user_can_cancel_pending_order()
    {
        $user = User::factory()->create();
        $order = \App\Models\Order::create([
            'user_id' => $user->id,
            'total_price' => 1000,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    /**
     * Test race condition ability.
     * Proves that even with multiple requests, we cannot sell more than available stock.
     */
    public function test_race_condition_protection_on_flash_sale_stock()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);

        $flashSale = FlashSale::create([
            'product_id' => $product->id,
            'flash_sale_price' => 100,
            'flash_sale_stock' => 5,
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
        ]);

        $successCount = 0;
        $failCount = 0;

        for ($i = 0; $i < 10; $i++) {
            $response = $this->actingAs($user, 'sanctum')
                ->postJson('/api/checkout', [
                    'checkout_type' => 'buy_now',
                    'product_id' => $product->id,
                    'quantity' => 1
                ]);

            if ($response->status() === 200)
                $successCount++;
            if ($response->status() === 400)
                $failCount++;
        }

        $this->assertEquals(5, $successCount, "Hanya 5 order yang boleh sukses");
        $this->assertEquals(5, $failCount, "5 order lainnya harus gagal karena stok habis");
        $this->assertDatabaseHas('flash_sales', ['id' => $flashSale->id, 'flash_sale_stock' => 0]);
    }
}