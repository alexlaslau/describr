<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
    ) {}

    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => $this->productService->getProducts(Auth::user()),
        ]);
    }

    public function show(Product $product)
    {
        abort_if($product->user_id !== Auth::id(), 403);

        $product->load(['productLinks', 'scrapeResults']);

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

        $product = $this->productService->createWithLinks(
            Auth::user(),
            $validated['name'],
            $validated['links'],
        );

        return redirect()->route('products.show', $product);
    }
}
