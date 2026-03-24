<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\ProductPriceHistory;
use App\Models\ProductWorkflowLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductManagementController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'stats' => [
                'total' => Product::count(),
                'draft' => Product::where('status', 'draft')->count(),
                'pending_validation' => Product::where('status', 'pending_validation')->count(),
                'published' => Product::where('status', 'published')->count(),
                'archived' => Product::where('status', 'archived')->count(),
            ],
            'alerts' => [
                'low_stock_variants' => ProductVariant::where('stock', '<=', 5)->count(),
                'missing_media_products' => Product::doesntHave('media')->count(),
            ],
            'recent_activity' => ProductWorkflowLog::latest()->take(10)->get(),
        ]);
    }

    public function index(Request $request)
    {
        $query = Product::with(['primaryCategory', 'variants', 'media'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('category_id')) {
            $query->where('primary_category_id', $request->integer('category_id'));
        }
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('marketing_name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'marketing_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'sku' => 'required|string|max:255|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'primary_category_id' => 'nullable|exists:categories,id',
            'secondary_category_ids' => 'nullable|array',
            'secondary_category_ids.*' => 'integer|exists:categories,id',
            'tags' => 'nullable|array',
            'attributes' => 'nullable|array',
            'seo_title' => 'nullable|string|max:255',
            'seo_slug' => 'nullable|string|max:255|unique:products,seo_slug',
            'seo_description' => 'nullable|string|max:500',
        ]);

        $validated['status'] = 'draft';
        $validated['is_active'] = true;
        $validated['primary_category_id'] = $validated['primary_category_id'] ?? $validated['category_id'];

        $product = Product::create($validated);
        $this->logWorkflow($product, null, 'draft', 'Product created');

        return response()->json($product->load(['primaryCategory', 'variants', 'media']), 201);
    }

    public function show(Product $product, $productId = null)
    {
        $product = $this->resolveProduct($product, $productId);
        return response()->json($product->load(['primaryCategory', 'variants.priceHistories', 'variants.stockMovements', 'media', 'workflowLogs']));
    }

    public function update(Request $request, Product $product, $productId = null)
    {
        $product = $this->resolveProduct($product, $productId);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'marketing_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'sku' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product->id)],
            'category_id' => 'nullable|exists:categories,id',
            'primary_category_id' => 'nullable|exists:categories,id',
            'secondary_category_ids' => 'nullable|array',
            'secondary_category_ids.*' => 'integer|exists:categories,id',
            'tags' => 'nullable|array',
            'attributes' => 'nullable|array',
            'seo_title' => 'nullable|string|max:255',
            'seo_slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'seo_slug')->ignore($product->id)],
            'seo_description' => 'nullable|string|max:500',
        ]);

        $product->update($validated);

        return response()->json($product->load(['primaryCategory', 'variants', 'media']));
    }

    public function submitForValidation(Product $product, $productId = null)
    {
        $product = $this->resolveProduct($product, $productId);
        $this->transitionStatus($product, 'pending_validation', 'Submitted for validation');
        return response()->json($product->fresh());
    }

    public function publish(Product $product, $productId = null)
    {
        $product = $this->resolveProduct($product, $productId);
        $this->ensurePublishRequirements($product);
        $this->transitionStatus($product, 'published', 'Published product');
        $product->published_at = now();
        $product->save();

        return response()->json($product->fresh());
    }

    public function archive(Product $product, $productId = null)
    {
        $product = $this->resolveProduct($product, $productId);
        $this->transitionStatus($product, 'archived', 'Archived product');
        return response()->json($product->fresh());
    }

    public function addVariant(Request $request, Product $product, $productId = null)
    {
        $product = $this->resolveProduct($product, $productId);
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'sku' => 'required|string|max:255|unique:product_variants,sku',
            'attributes' => 'nullable|array',
            'price' => 'nullable|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'stock' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
        ]);

        $validated['product_id'] = $productId ?? $product->id;
        $variant = ProductVariant::create($validated);

        return response()->json($variant, 201);
    }

    public function updateVariant(Request $request, ProductVariant $variant, $variantId = null)
    {
        $variant = $this->resolveVariant($variant, $variantId);
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'sku' => ['sometimes', 'string', 'max:255', Rule::unique('product_variants', 'sku')->ignore($variant->id)],
            'attributes' => 'nullable|array',
            'price' => 'nullable|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'stock' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
        ]);

        $variant->update($validated);
        return response()->json($variant->fresh());
    }

    public function addMedia(Request $request, Product $product, $productId = null)
    {
        $product = $this->resolveProduct($product, $productId);
        $validated = $request->validate([
            'variant_id' => 'nullable|exists:product_variants,id',
            'type' => 'required|string|in:image,video,document',
            'title' => 'nullable|string|max:255',
            'path' => 'required|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['product_id'] = $productId ?? $product->id;
        $media = ProductMedia::create($validated);
        return response()->json($media, 201);
    }

    public function addStockMovement(Request $request, ProductVariant $variant, $variantId = null)
    {
        $variant = $this->resolveVariant($variant, $variantId);
        $validated = $request->validate([
            'warehouse' => 'nullable|string|max:255',
            'type' => 'required|string|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
            'performed_by' => 'nullable|string|max:255',
        ]);

        $movement = $variant->stockMovements()->create($validated);

        if ($validated['type'] === 'in') {
            $variant->increment('stock', $validated['quantity']);
        } elseif ($validated['type'] === 'out') {
            $variant->decrement('stock', $validated['quantity']);
        } else {
            $variant->stock = $validated['quantity'];
            $variant->save();
        }

        return response()->json([
            'movement' => $movement,
            'variant' => $variant->fresh(),
        ], 201);
    }

    public function addPrice(Request $request, ProductVariant $variant, $variantId = null)
    {
        $variant = $this->resolveVariant($variant, $variantId);
        $validated = $request->validate([
            'channel' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'currency' => 'required|string|size:3',
            'price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'changed_by' => 'nullable|string|max:255',
            'starts_at' => 'nullable|date',
        ]);

        $history = $variant->priceHistories()->create($validated);

        $variant->update([
            'currency' => $validated['currency'],
            'price' => $validated['price'],
            'promo_price' => $validated['promo_price'] ?? null,
        ]);

        return response()->json([
            'price_history' => $history,
            'variant' => $variant->fresh(),
        ], 201);
    }

    protected function ensurePublishRequirements(Product $product): void
    {
        $errors = [];

        if (blank($product->description) && blank($product->long_description)) {
            $errors[] = 'Description is required before publishing.';
        }
        if ($product->media()->count() === 0) {
            $errors[] = 'At least one media is required before publishing.';
        }

        $hasPrice = !is_null($product->price) || $product->variants()->whereNotNull('price')->exists();
        if (!$hasPrice) {
            $errors[] = 'Price is required before publishing.';
        }

        if (!empty($errors)) {
            abort(response()->json([
                'message' => 'Validation failed for publish',
                'errors' => $errors,
            ], 422));
        }
    }

    protected function transitionStatus(Product $product, string $toStatus, string $comment): void
    {
        $from = $product->status;
        $product->status = $toStatus;
        $product->save();
        $this->logWorkflow($product, $from, $toStatus, $comment);
    }

    protected function logWorkflow(Product $product, ?string $from, string $to, string $comment): void
    {
        ProductWorkflowLog::create([
            'product_id' => $product->id,
            'from_status' => $from,
            'to_status' => $to,
            'comment' => $comment,
            'acted_by' => request()?->attributes->get('user')?->sub ?? 'system',
            'acted_at' => now(),
        ]);
    }

    protected function resolveProduct(Product $product, $productId = null): Product
    {
        if (!is_null($productId)) {
            return Product::query()->findOrFail((int) $productId);
        }

        return $product;
    }

    protected function resolveVariant(ProductVariant $variant, $variantId = null): ProductVariant
    {
        if (!is_null($variantId)) {
            return ProductVariant::query()->findOrFail((int) $variantId);
        }

        return $variant;
    }
}
