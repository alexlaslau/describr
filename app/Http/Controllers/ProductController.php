<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\ProductStoreRequest;
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

        $product->load(['productLinks', 'generatedDescriptions']);

        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create');
    }

    public function store(ProductStoreRequest $request)
    {
        $product = Product::createWithLinks(
            Auth::user(),
            $request->validated('name'),
            $request->cleanedLinks(),
        );

        ScrapeProduct::dispatch($product, $request->validated('ai_provider'), $request->validated('prompt_length'));

        return redirect()->route('products.show', $product);
    }
}
