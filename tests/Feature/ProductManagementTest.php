<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_and_lists_pm_products(): void
    {
        $this->withoutMiddleware();
        $category = Category::create(['name' => 'Informatique']);

        $create = $this->postJson('/api/pm/products', [
            'name' => 'Laptop Pro',
            'sku' => 'LTP-PRO-001',
            'description' => 'High-end workstation',
            'price' => 2000,
            'quantity' => 12,
            'category_id' => $category->id,
            'primary_category_id' => $category->id,
        ]);

        $create->assertCreated()
            ->assertJsonPath('status', 'draft');

        $list = $this->getJson('/api/pm/products?search=Laptop');
        $list->assertOk()
            ->assertJsonPath('data.0.name', 'Laptop Pro');
    }

    public function test_publish_requires_description_media_and_price(): void
    {
        $this->withoutMiddleware();
        $category = Category::create(['name' => 'Electronique']);

        $product = Product::create([
            'name' => 'Camera X',
            'sku' => 'CAM-X-001',
            'price' => 0,
            'quantity' => 0,
            'category_id' => $category->id,
            'primary_category_id' => $category->id,
            'status' => 'pending_validation',
            'is_active' => true,
        ]);

        $publish = $this->postJson("/api/pm/products/{$product->id}/publish");
        $publish->assertStatus(422);
    }

    public function test_workflow_publish_success_with_variant_price_and_media(): void
    {
        $this->withoutMiddleware();
        $category = Category::create(['name' => 'Photo']);

        $product = Product::create([
            'name' => 'Camera Z',
            'sku' => 'CAM-Z-001',
            'description' => 'Pro camera',
            'price' => 0,
            'quantity' => 0,
            'category_id' => $category->id,
            'primary_category_id' => $category->id,
            'status' => 'draft',
            'is_active' => true,
        ]);

        $variantResponse = $this->postJson("/api/pm/products/{$product->id}/variants", [
            'name' => 'Black',
            'sku' => 'CAM-Z-001-BLK',
            'price' => 1200,
            'stock' => 10,
        ])->assertCreated();

        $variantId = $variantResponse->json('id');

        $this->postJson("/api/pm/products/{$product->id}/media", [
            'type' => 'image',
            'path' => '/uploads/camera-z-main.jpg',
            'sort_order' => 1,
        ])->assertCreated();

        $this->postJson("/api/pm/products/{$product->id}/submit")
            ->assertOk()
            ->assertJsonPath('status', 'pending_validation');

        $this->postJson("/api/pm/variants/{$variantId}/prices", [
            'currency' => 'EUR',
            'price' => 1150,
            'channel' => 'web',
            'region' => 'EU',
        ])->assertCreated();

        $publish = $this->postJson("/api/pm/products/{$product->id}/publish");
        $publish->assertOk()->assertJsonPath('status', 'published');
    }
}
