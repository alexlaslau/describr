<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;

class ScrapeProduct implements ShouldQueue
{
    use Queueable;

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
            })
            ->catch(function (Batch $batch, \Throwable $e) use ($product) {
                $product->update(['status' => 'failed']);
            })
            ->dispatch();
    }
}