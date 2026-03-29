<?php

namespace App\Providers;

use App\Interfaces\ScraperInterface;
use App\Interfaces\TranslationProviderInterface;
use App\Services\Integrations\DeepL\DeepLTranslationService;
use App\Services\Scraping\DomCrawlerScraper;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
