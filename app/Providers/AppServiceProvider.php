<?php

namespace App\Providers;

use App\Interfaces\AIProviderInterface;
use App\Services\AIProviders\OpenAIProvider;
use App\Interfaces\ScraperInterface;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Services\Scrapers\DomCrawlerScraper;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ScraperInterface::class, DomCrawlerScraper::class);
        $this->app->bind(AIProviderInterface::class, OpenAIProvider::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
