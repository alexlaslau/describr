<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductImageDownloadService;
use Illuminate\Support\Facades\Auth;

class ProductImageController extends Controller
{
    public function __construct(
        private readonly ProductImageDownloadService $downloadService,
    ) {}

    public function download(Product $product, ProductImage $image)
    {
        abort_if($product->user_id !== Auth::id(), 403);
        abort_if($image->product_id !== $product->id, 404);

        $download = $this->downloadService->downloadImage($image);

        return response($download['body'], 200, [
            'Content-Type' => $download['content_type'],
            'Content-Disposition' => "attachment; filename=\"{$download['filename']}\"",
        ]);
    }

    public function downloadAll(Product $product)
    {
        abort_if($product->user_id !== Auth::id(), 403);

        $archive = $this->downloadService->createImagesArchive($product);

        return response()->download($archive['path'], $archive['filename'])
            ->deleteFileAfterSend();
    }
}
