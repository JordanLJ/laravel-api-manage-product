<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->attributes->get('user');
        $products = Product::with('category')->paginate(10);
        return response()->json([
            'user' => $user,
            'products' => ProductResource::collection($products)
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());
        return response()->json(new ProductResource($product->load('category')), 201);
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function update(Request $request, Product $product)
    {
            $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'sku' => 'sometimes|string|unique:products,sku,'.$product->id,
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return response()->json(new ProductResource($product->load('category')));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(null, 204);
    }

    // Filtrer les produits par catégorie
    public function getByCategory(Category $category)
    {
        $products = Product::with('category')
            ->where('category_id', $category->id)
            ->paginate(10);

        return ProductResource::collection($products);
    }

    // Activer ou désactiver un produit
    public function toggleActive(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();

        return response()->json(new ProductResource($product->load('category')));
    }
}