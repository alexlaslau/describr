<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ProductImageDownloadService
{
    public function downloadImage(ProductImage $image): array
    {
        $response = Http::get($image->url);
        abort_if($response->failed(), 404);

        $extension = $this->extensionFromUrl($image->url);
        $filename = "image-{$image->id}.{$extension}";

        return [
            'body' => $response->body(),
            'content_type' => $response->header('Content-Type') ?? 'image/jpeg',
            'filename' => $filename,
        ];
    }

    public function createImagesArchive(Product $product): array
    {
        $product->load('images');
        abort_if($product->images->isEmpty(), 404);

        $zipPath = tempnam(sys_get_temp_dir(), 'images_') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);

        foreach ($product->images as $image) {
            $response = Http::get($image->url);

            if (! $response->successful()) {
                continue;
            }

            $zip->addFromString(
                "image-{$image->id}.{$this->extensionFromUrl($image->url)}",
                $response->body(),
            );
        }

        $zip->close();

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $product->name);

        return [
            'path' => $zipPath,
            'filename' => "{$safeName}-images.zip",
        ];
    }

    private function extensionFromUrl(string $url): string
    {
        return pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
    }
}
