<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductDescriptionController extends Controller
{
    public function show(Request $request, Product $product)
    {
        $apiClient = $request->attributes->get('api_client');

        abort_unless($product->user_id === $apiClient->user_id, 403, 'This API client cannot access the requested product.');

        $product->load([
            'generatedDescriptions' => fn ($query) => $query->with('translations'),
        ]);

        abort_unless($product->generated_description, 404, 'No generated description available for this product.');

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'status' => $product->status,
                'generated_at' => $product->generated_at,
                'description' => $product->generated_description,
            ],
        ]);
    }
}
