<?php

namespace App\Jobs;

use App\Models\ProductLink;
use App\Services\Scraping\ScrapingService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScrapeProductLink implements ShouldQueue
{
    use Queueable, Batchable;

    public int $tries = 3;
    public array $backoff = [100, 200];
    public int $timeout = 60;

    public function __construct(
        public ProductLink $link,
    ) {}

    public function handle(ScrapingService $service): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $service->scrapeLink($this->link);
    }

    public function tags(): array
    {
        return [
            'product:'.$this->link->product_id,
            'link:'.$this->link->id,
            'pipeline:scrape',
        ];
    }
}
