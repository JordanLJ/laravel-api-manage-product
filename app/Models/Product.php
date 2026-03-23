<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'description', 'price', 'quantity', 'sku', 'category_id', 'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
