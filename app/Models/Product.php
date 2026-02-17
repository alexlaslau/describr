<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'generated_description',
        'status',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scrapeResults(): HasMany
    {
        return $this->hasMany(ScrapeResult::class);
    }

    public function productLinks(): HasMany
    {
        return $this->hasMany(ProductLink::class);
    }

    public function scopeWithLinkCount($query)
    {
        return $query->withCount('productLinks');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->whereNotIn('status', ['completed', 'failed', 'pending']);
    }
}