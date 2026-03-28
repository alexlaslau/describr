<?php

namespace App\Services;

use App\Models\DescriptionTranslation;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ProductDescriptionPdfService
{
    public function original(Product $product): array
    {
        return [
            'pdf' => Pdf::loadView('pdf.product-description', [
                'title' => $product->name,
                'subtitle' => 'Generated product description',
                'content' => $product->generated_description,
                'meta' => [
                    'Product' => $product->name,
                    'Status' => ucfirst($product->status),
                    'Generated at' => optional($product->generated_at)?->format('M j, Y H:i'),
                ],
            ]),
            'filename' => $this->filenameFor($product, 'description'),
        ];
    }

    public function translation(Product $product, DescriptionTranslation $translation): array
    {
        $languageLabel = $translation->target_language;

        return [
            'pdf' => Pdf::loadView('pdf.product-description', [
                'title' => $product->name,
                'subtitle' => "{$languageLabel} translation",
                'content' => $translation->translated_text,
                'meta' => [
                    'Product' => $product->name,
                    'Language' => $languageLabel,
                    'Provider' => strtoupper($translation->provider),
                    'Translated at' => optional($translation->translated_at)?->format('M j, Y H:i'),
                ],
            ]),
            'filename' => $this->filenameFor($product, "description-{$translation->target_language}"),
        ];
    }

    private function filenameFor(Product $product, string $suffix): string
    {
        $productSlug = Str::slug($product->name) ?: "product-{$product->id}";

        return "{$productSlug}-{$suffix}.pdf";
    }
}
