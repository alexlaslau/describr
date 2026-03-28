<?php

namespace App\Providers;

use App\Interfaces\ScraperInterface;
use App\Interfaces\TranslationProviderInterface;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Services\Scrapers\DomCrawlerScraper;
use App\Services\Translations\DeepLTranslationService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ScraperInterface::class, DomCrawlerScraper::class);
        $this->app->bind(TranslationProviderInterface::class, DeepLTranslationService::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
