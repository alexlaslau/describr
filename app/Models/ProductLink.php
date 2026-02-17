<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'raw_html',
        'status',
        'error_message',
        'scraped_at',
    ];

    protected function casts(): array
    {
        return [
            'scraped_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}