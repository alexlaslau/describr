<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'product_link_id' => ProductLink::factory(),
            'url' => fake()->imageUrl(),
            'alt' => fake()->sentence(3),
        ];
    }
}
