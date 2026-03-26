<?php

namespace App\Listeners;

use App\Events\ProductScraped;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class LogProductScraped implements ShouldQueue
{
    use Queueable;

    public function handle(ProductScraped $event): void
    {
        Log::info("[ProductScraped] Product #{$event->scrapingData->product->id} ({$event->scrapingData->product->name}) — all links scraped successfully.");
    }
}
