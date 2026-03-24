<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

Route::middleware(['identity.auth'])->group(function () {
    Route::get('products', [ProductController::class, 'index']);         // liste produits
    Route::post('products', [ProductController::class, 'store']);        // créer produit
    Route::get('products/{product}', [ProductController::class, 'show']); // voir produit
    Route::put('products/{product}', [ProductController::class, 'update']); // modifier
    Route::delete('products/{product}', [ProductController::class, 'destroy']); // supprimer

    // Routes avancées
    Route::get('products/category/{category}', [ProductController::class, 'getByCategory']); // filtrer par catégorie
    Route::patch('products/{product}/toggle-active', [ProductController::class, 'toggleActive']); // activer/désactiver produit
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/categories', [CategoryController::class, 'index']);