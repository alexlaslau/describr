<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\DescriptionTranslation;
use App\Models\Product;
use App\Services\Products\ProductDescriptionPdfService;
use Illuminate\Support\Facades\Auth;

class ProductDescriptionPdfController extends Controller
{
    public function downloadOriginal(Product $product, ProductDescriptionPdfService $pdfService)
    {
        abort_if($product->user_id !== Auth::id(), 403);
        abort_if(blank($product->generated_description), 404, 'No generated description available for this product.');

        $pdf = $pdfService->original($product);

        return $pdf['pdf']->download($pdf['filename']);
    }

    public function downloadTranslation(Product $product, DescriptionTranslation $translation, ProductDescriptionPdfService $pdfService)
    {
        abort_if($product->user_id !== Auth::id(), 403);
        abort_if($translation->productId() !== $product->id, 404);
        abort_if($translation->status !== 'completed' || blank($translation->translated_text), 404, 'No translated description available for this export.');

        $pdf = $pdfService->translation($product, $translation->loadMissing('generatedDescription'));

        return $pdf['pdf']->download($pdf['filename']);
    }
}
