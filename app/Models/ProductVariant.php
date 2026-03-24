<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'attributes',
        'price',
        'promo_price',
        'currency',
        'stock',
        'status',
        'is_default',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_default' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    public function priceHistories()
    {
        return $this->hasMany(ProductPriceHistory::class, 'variant_id');
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class, 'variant_id');
    }
}
