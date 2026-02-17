<?php

namespace App\Providers;

use App\Interfaces\ScraperInterface;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Services\Scrapers\DomCrawlerScraper;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ScraperInterface::class, DomCrawlerScraper::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
