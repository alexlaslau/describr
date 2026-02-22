<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductLink;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::first();
        
        Product::factory()
            ->count(3)
            ->completed()
            ->for($user)
            ->has(
                ProductLink::factory()
                    ->count(2)
                    ->scraped()
            )
            ->hasGeneratedDescriptions(1)
            ->create();

        Product::factory()
            ->scraping()
            ->for($user)
            ->has(
                ProductLink::factory()
                    ->scraped()
            )
            ->has(
                ProductLink::factory()
                    ->state(['status' => 'scraping'])
            )
            ->create();

        Product::factory()
            ->for($user)
            ->state(['status' => 'generating'])
            ->has(
                ProductLink::factory()
                    ->count(2)
                    ->scraped()
            )
            ->create();

        Product::factory()
            ->count(2)
            ->for($user)
            ->has(
                ProductLink::factory()
                    ->count(2)
            )
            ->create();

        Product::factory()
            ->failed()
            ->for($user)
            ->has(
                ProductLink::factory()
                    ->count(2)
                    ->failed()
            )
            ->create();
    }
}
