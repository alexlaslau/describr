<?php

namespace App\Jobs;

use App\DTOs\ProductScrapingData;
use App\Events\ProductScraped;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;

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
            ->then(function (Batch $batch) use ($scrapingData, $product) {
                $allFailed = $product->productLinks()
                    ->where('status', '!=', 'failed')
                    ->doesntExist();

                if ($allFailed) {
                    $product->update(['status' => 'failed']);

                    return;
                }

                $product->update(['status' => 'scraped']);
                ProductScraped::dispatch($scrapingData);
            })
            ->dispatch();
    }

    public function tags(): array
    {
        return [
            'product:' . $this->scrapingData->product->id,
            'provider:' . $this->scrapingData->aiProvider,
            'pipeline:scrape',
        ];
    }
}
