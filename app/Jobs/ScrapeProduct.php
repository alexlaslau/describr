<?php

namespace App\Jobs;

use App\Models\Product;
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
        public Product $product,
    ) {}

    public function handle(): void
    {
        $product = $this->product;

        $product->update(['status' => 'scraping']);

        $jobs = $product->productLinks
            ->map(fn ($link) => new ScrapeProductLink($link))
            ->toArray();

        Bus::batch($jobs)
            ->then(function (Batch $batch) use ($product) {
                $product->update(['status' => 'scraped']);
                GenerateProductDescription::dispatch($product);
            })
            ->catch(function (Batch $batch, \Throwable $e) use ($product) {
                $product->update(['status' => 'failed']);
            })
            ->dispatch();
    }
}