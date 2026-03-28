<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\ProductStoreRequest;
use App\DTOs\ProductScrapingData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Jobs\ScrapeProduct;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => Auth::user()->getProducts(),
        ]);
    }

    public function show(Product $product)
    {
        abort_if($product->user_id !== Auth::id(), 403);

        $product->load([
            'productLinks',
            'images',
            'generatedDescriptions' => fn ($query) => $query->latest()->with('translations'),
        ]);

        return Inertia::render('Products/Show', [
            'product' => $product,
            'config' => [
                'translationLanguages' => config('app.describr.translation_languages'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'config' => [
                'maxLinksPerProduct' => config('app.describr.max_links_per_product'),
                'targetAudiences' => config('app.describr.target_audiences'),
                'tones' => config('app.describr.tones'),
            ],
        ]);
    }

    public function store(ProductStoreRequest $request)
    {
        $product = Product::createWithLinks(
            Auth::user(),
            $request->validated('name'),
            $request->cleanedLinks(),
        );

        $scrapingData = ProductScrapingData::fromRequest($product, $request->validated());

        ScrapeProduct::dispatch($scrapingData);

        return redirect()->route('products.show', $product);
    }

    public function downloadImage(Product $product, \App\Models\ProductImage $image)
    {
        abort_if($product->user_id !== Auth::id(), 403);
        abort_if($image->product_id !== $product->id, 404);

        $response = Http::get($image->url);
        abort_if($response->failed(), 404);

        $extension = pathinfo(parse_url($image->url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = "image-{$image->id}.{$extension}";

        return response($response->body(), 200, [
            'Content-Type' => $response->header('Content-Type') ?? 'image/jpeg',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function downloadAllImages(Product $product)
    {
        abort_if($product->user_id !== Auth::id(), 403);

        $product->load('images');
        abort_if($product->images->isEmpty(), 404);

        $zipPath = tempnam(sys_get_temp_dir(), 'images_') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);

        foreach ($product->images as $image) {
            $response = Http::get($image->url);
            if ($response->successful()) {
                $ext = pathinfo(parse_url($image->url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $zip->addFromString("image-{$image->id}.{$ext}", $response->body());
            }
        }

        $zip->close();

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $product->name);

        return response()->download($zipPath, "{$safeName}-images.zip")
            ->deleteFileAfterSend();
    }
}
