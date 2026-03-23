<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products',
            'category_id' => 'required|exists:categories,id', // <-- clé étrangère
            'is_active' => 'boolean',
        ]);

        $product = Product::create($validated);
        return response()->json($product, 201);
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
            'category' => 'sometimes|string|max:50',
            'sku' => 'sometimes|string|unique:products,sku,'.$product->id,
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(null, 204);
    }

    // Filtrer les produits par catégorie
    public function getByCategory($category)
    {
        return Product::where('category', $category)->get();
        
    }

    // Activer ou désactiver un produit
    public function toggleActive(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();

        return response()->json($product);
    }
}