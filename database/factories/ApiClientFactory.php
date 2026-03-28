<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ApiClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company() . ' integration',
            'client_id' => 'cli_' . Str::lower(Str::random(16)),
            'client_secret' => 'sec_' . Str::random(40),
            'status' => 'active',
        ];
    }
}
