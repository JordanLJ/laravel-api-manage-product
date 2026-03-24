<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductManagementController;

Route::middleware(['identity.auth'])->group(function () {
    // Legacy product endpoints
    Route::get('products', [ProductController::class, 'index']);         // liste produits
    Route::post('products', [ProductController::class, 'store']);        // créer produit
    Route::get('products/{product}', [ProductController::class, 'show']); // voir produit
    Route::put('products/{product}', [ProductController::class, 'update']); // modifier
    Route::delete('products/{product}', [ProductController::class, 'destroy']); // supprimer

    // Routes avancées
    Route::get('products/category/{category}', [ProductController::class, 'getByCategory']); // filtrer par catégorie
    Route::patch('products/{product}/toggle-active', [ProductController::class, 'toggleActive']); // activer/désactiver produit

    // Advanced product management endpoints
    Route::get('pm/dashboard', [ProductManagementController::class, 'dashboard']);
    Route::get('pm/products', [ProductManagementController::class, 'index']);
    Route::post('pm/products', [ProductManagementController::class, 'store']);
    Route::get('pm/products/{product}', [ProductManagementController::class, 'show']);
    Route::put('pm/products/{product}', [ProductManagementController::class, 'update']);
    Route::post('pm/products/{product}/submit', [ProductManagementController::class, 'submitForValidation']);
    Route::post('pm/products/{product}/publish', [ProductManagementController::class, 'publish']);
    Route::post('pm/products/{product}/archive', [ProductManagementController::class, 'archive']);

    Route::post('pm/products/{product}/variants', [ProductManagementController::class, 'addVariant']);
    Route::put('pm/variants/{variant}', [ProductManagementController::class, 'updateVariant']);
    Route::post('pm/variants/{variant}/stock-movements', [ProductManagementController::class, 'addStockMovement']);
    Route::post('pm/variants/{variant}/prices', [ProductManagementController::class, 'addPrice']);
    Route::post('pm/products/{product}/media', [ProductManagementController::class, 'addMedia']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/categories', [CategoryController::class, 'index']);