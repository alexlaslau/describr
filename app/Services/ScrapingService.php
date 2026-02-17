<?php

namespace App\Services;

use App\Models\ProductLink;
use App\Interfaces\ScraperInterface;

class ScrapingService
{
    public function __construct(
        private ScraperInterface $scraper,
    ) {}

    public function scrapeLink(ProductLink $link): void
    {
        $link->update(['status' => 'scraping']);

        try {
            $result = $this->scraper->scrape($link->url);

            $link->update([
                'parsed_content' => $result->toPromptText(),
                'status' => 'scraped',
                'scraped_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error("[ScrapingService] Scraping failed for link #{$link->id} ({$link->url}): {$e->getMessage()}");

            $link->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}