<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\Product;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Get a paginated list of products.
     * 
     * @queryParam search string Filter products by name. Example: laptop
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam order_by string Field to sort by. Example: price
     * @queryParam order_direction string Sort direction (asc or desc). Example: desc
     * @queryParam price_min number Minimum price filter. Example: 100
     * @queryParam price_max number Maximum price filter. Example: 1000
     * @queryParam price_range string Price range filter (e.g. 100-500). Example: 100-500
     */
    public function listProducts(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'per_page' => 'nullable|integer|in:10,25,50,100',
            'search' => 'nullable|string|max:255',
            'order' => 'nullable|string|in:price,created_at',
            'direction' => 'nullable|string|in:ASC,DESC',
            'price_range' => ['nullable', 'string', 'regex:/^\d*-\d*$/'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $search = $validated['search'] ?? null;
        $order = $validated['order'] ?? null;
        $direction = $validated['direction'] ?? 'desc';
        $priceRange = $validated['price_range'] ?? null;

        $products = Product::query();

        $products->when($search, function ($query, $search) {
            return $query->where('name', 'ilike', '%' . $search . '%')
                ->orWhere('description', 'ilike', '%' . $search . '%');
        });

        $products->when($order, function ($query, $order, $direction) {
            return $query->orderBy($order, $direction);
        });

        $products->when($priceRange, function ($query, $priceRange) {
            list($min, $max) = explode('-', $priceRange);
            $min = ($min !== '') ? (float) $min : 0;
            $max = ($max !== '') ? (float) $max : PHP_INT_MAX;

            return $query->whereBetween('price', [$min, $max]);
        });

        $products = $products->paginate($perPage);

        return $this->successResponse($products, 'Products retrieved successfully');
    }

    /**
     * Get details of a specific product.
     * 
     * @param int $productId Product ID
     */
    public function productDetails(int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return $this->notFoundResponse('Product not found');
        }

        return $this->successResponse($product, 'Product retrieved successfully');
    }

    /**
     * Create a new product not yet implemented RBAC (Free to create products for now)
     */
    public function createProduct(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($request->only('name', 'description', 'price', 'stock'));

        return $this->createdResponse($product, 'Product created successfully');
    }

    /**
     * Update an existing product not yet implemented RBAC (Free to update products for now)
     * 
     * @param int $productId Product ID
     */
    public function updateProduct(Request $request, int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return $this->notFoundResponse('Product not found');
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);

        $product->update($request->only('name', 'description', 'price', 'stock'));

        return $this->successResponse($product, 'Product updated successfully');
    }

    /**
     * Delete a product not yet implemented RBAC (Free to delete products for now)
     * 
     * @param int $productId Product ID
     */
    public function deleteProduct(int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return $this->notFoundResponse('Product not found');
        }

        $product->delete();

        return $this->successResponse(null, 'Product deleted successfully');
    }
}
