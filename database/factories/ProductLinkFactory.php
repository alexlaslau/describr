<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'url' => fake()->url(),
            'status' => 'pending',
            'raw_html' => null,
            'error_message' => null,
            'scraped_at' => null,
        ];
    }

    public function scraped(): static
    {
        return $this->state(fn () => [
            'status' => 'scraped',
            'raw_html' => '<html><body>' . fake()->paragraphs(3, true) . '</body></html>',
            'scraped_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
            'error_message' => fake()->sentence(),
        ]);
    }
}
