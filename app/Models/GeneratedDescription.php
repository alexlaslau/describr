<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;

class GeneratedDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function generate(Product $product)
    {
        $prompt = "Generate a product title and description for the following product: {$product->name}\n\n";

        foreach ($product->links as $link) {
            $prompt .= "{$link->url}\n";
        }

        $response = Http::post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        return $response->json();
    }
}
