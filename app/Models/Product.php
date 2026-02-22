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

    public function generatedDescriptions(): HasMany
    {
        return $this->hasMany(GeneratedDescription::class);
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

    public static function createWithLinks(User $user, string $name, array $urls): static
    {
        $product = $user->products()->create([
            'name' => $name,
            'status' => 'pending',
        ]);

        foreach ($urls as $url) {
            $product->productLinks()->create([
                'url' => $url,
                'status' => 'pending',
            ]);
        }

        return $product;
    }

    public function getFullParsedText(): string
    {
        $maxCharsPerSource = config('app.describr.max_characters_per_source');

        return $this->productLinks()
            ->where('status', 'scraped')
            ->pluck('parsed_content')
            ->map(function ($content, $index) use ($maxCharsPerSource) {
                $trimmed = mb_strlen($content) > $maxCharsPerSource
                    ? mb_substr($content, 0, $maxCharsPerSource) . '...[truncated]'
                    : $content;

                return "--- Source " . ($index + 1) . " ---\n" . $trimmed;
            })
            ->implode("\n\n");
    }
}