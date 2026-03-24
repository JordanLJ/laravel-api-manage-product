<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'marketing_name',
        'description',
        'short_description',
        'long_description',
        'price',
        'quantity',
        'sku',
        'category_id',
        'primary_category_id',
        'secondary_category_ids',
        'tags',
        'attributes',
        'seo_title',
        'seo_slug',
        'seo_description',
        'status',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'secondary_category_ids' => 'array',
        'tags' => 'array',
        'attributes' => 'array',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function primaryCategory()
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function workflowLogs()
    {
        return $this->hasMany(ProductWorkflowLog::class);
    }
}
