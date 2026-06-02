<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products_with_pagination()
    {
        Product::factory()->count(15)->create();

        $response = $this->getJson('/api/products?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data.data')
            ->assertJsonStructure(['success', 'data' => ['current_page', 'data']]);
    }

    public function test_can_search_products_by_name()
    {
        Product::factory()->create(['name' => 'Unique Laptop']);
        Product::factory()->create(['name' => 'Generic Phone']);

        $response = $this->getJson('/api/products?search=Laptop');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Unique Laptop');
    }

    public function test_can_filter_products_by_price_range()
    {
        Product::factory()->create(['price' => 100]);
        Product::factory()->create(['price' => 500]);
        Product::factory()->create(['price' => 1000]);

        $response = $this->getJson('/api/products?price_range=200-600');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.price', "500.00");
    }

    public function test_can_get_product_details()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.sku', $product->sku);
    }

    public function test_admin_can_create_product()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/products', [
                'name' => 'New Product',
                'description' => 'Test Desc',
                'price' => 150000,
                'stock' => 10,
                'sku' => 'TEST-SKU-01',
                'slug' => 'new-product-test'
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['name' => 'New Product']);
    }

    public function test_non_existent_product_returns_404()
    {
        $response = $this->getJson('/api/products/999');
        $response->assertStatus(404);
    }
}