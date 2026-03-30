<?php

namespace App\Http\Controllers\Products;

use App\DTOs\ProductScrapingData;
use App\Exceptions\TranslationFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Interfaces\TranslationProviderInterface;
use App\Jobs\ScrapeProduct;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(private ProductRepositoryInterface $products) {}

    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => $this->products->getForUser(Auth::user()),
        ]);
    }

    public function show(Product $product, TranslationProviderInterface $translationProvider)
    {
        abort_if($product->user_id !== Auth::id(), 403);

        $product = $this->products->findWithRelations($product->id, [
            'productLinks',
            'images',
            'generatedDescriptions.translations',
        ]);

        try {
            $usage = $translationProvider->usage();
        } catch (TranslationFailedException $e) {
            Log::error('[ProductController] Failed to load current translation usage.', [
                'error' => $e->getMessage(),
            ]);
        }

        return Inertia::render('Products/Show', [
            'product' => $product,
            'config' => [
                'translationLanguages' => config('app.describr.description_translation_languages'),
                'translationUsage' => $usage ?? null,
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
        $product = $this->products->createWithLinks(
            Auth::user(),
            $request->validated('name'),
            $request->cleanedLinks(),
        );

        $scrapingData = ProductScrapingData::fromRequest($product, $request->validated());

        ScrapeProduct::dispatch($scrapingData);

        return redirect()->route('products.show', $product);
    }
}
