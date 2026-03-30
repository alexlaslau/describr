<?php

namespace App\Http\Controllers\Api\Products;

use App\DTOs\ProductScrapingData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Jobs\ScrapeProduct;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductController extends Controller
{
    public function __construct(private ProductRepositoryInterface $products) {}

    public function store(ProductStoreRequest $request)
    {
        $apiClient = $request->attributes->get('api_client');

        $product = $this->products->createWithLinks(
            $apiClient->user,
            $request->validated('name'),
            $request->cleanedLinks(),
        );

        $scrapingData = ProductScrapingData::fromRequest($product, $request->validated());

        ScrapeProduct::dispatch($scrapingData);

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'status' => $product->status,
                'links_count' => count($request->cleanedLinks()),
            ],
        ], 201);
    }
}
