<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\ProductStoreRequest;
use App\DTOs\ProductScrapingData;
use Illuminate\Support\Facades\Auth;
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

        $product->load(['productLinks', 'generatedDescriptions', 'images']);

        return Inertia::render('Products/Show', [
            'product' => $product,
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
}
