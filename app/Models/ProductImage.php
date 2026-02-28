<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'alt',
        'url',
        'product_id',
        'product_link_id',
    ];

    public function productLink(): BelongsTo
    {
        return $this->belongsTo(ProductLink::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
