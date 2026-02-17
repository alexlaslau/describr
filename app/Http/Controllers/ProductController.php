<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'links' => 'required|array|min:1',
            'links.*' => 'required|url',
        ]);

        $product = Product::createWithLinks(
            Auth::user(),
            $validated['name'],
            $validated['links'],
        );

        ScrapeProduct::dispatch($product);

        return redirect()->route('products.show', $product);
    }
}
