<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'status' => 'pending',
            'generated_description' => null,
            'generated_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'generated_description' => fake()->paragraphs(2, true),
            'generated_at' => now(),
        ]);
    }

    public function scraping(): static
    {
        return $this->state(fn () => [
            'status' => 'scraping',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
        ]);
    }
}
