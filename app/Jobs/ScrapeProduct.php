<?php

namespace App\Jobs;

use App\DTOs\ProductScrapingData;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\GenerateProductDescription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScrapeProduct implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [100, 200];
    public int $timeout = 60;

    public function __construct(
        public ProductScrapingData $scrapingData,
    ) {}

    public function handle(): void
    {
        $product = $this->scrapingData->product;
        $scrapingData = $this->scrapingData;

        $product->update(['status' => 'scraping']);

        $jobs = $product->productLinks
            ->map(fn ($link) => new ScrapeProductLink($link))
            ->toArray();

        Bus::batch($jobs)
            ->then(function (Batch $batch) use ($scrapingData) {
                $scrapingData->product->update(['status' => 'scraped']);
                GenerateProductDescription::dispatch($scrapingData);
            })
            ->catch(function (Batch $batch, \Throwable $e) use ($product) {
                $product->update(['status' => 'failed']);
            })
            ->dispatch();
    }
}