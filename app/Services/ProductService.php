<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;

class ProductService
{
    public function getProducts(User $user)
    {
        return $user->products()
            ->withLinkCount()
            ->latest()
            ->get();
    }

    public function getProductStats(User $user): array
    {
        return [
            'total' => $user->products()->count(),
            'completed' => $user->products()->completed()->count(),
            'inProgress' => $user->products()->inProgress()->count(),
        ];
    }

    public function createWithLinks(User $user, string $name, array $urls): Product
    {
        $product = $user->products()->create([
            'name' => $name,
            'status' => 'pending',
        ]);

        foreach ($urls as $url) {
            $product->productLinks()->create([
                'url' => $url,
                'status' => 'pending',
            ]);
        }

        return $product;
    }
}
