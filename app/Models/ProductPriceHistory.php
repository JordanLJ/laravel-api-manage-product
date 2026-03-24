<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceHistory extends Model
{
    protected $fillable = [
        'variant_id',
        'channel',
        'region',
        'currency',
        'price',
        'promo_price',
        'changed_by',
        'starts_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
