<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductWorkflowLog extends Model
{
    protected $fillable = [
        'product_id',
        'from_status',
        'to_status',
        'comment',
        'acted_by',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
