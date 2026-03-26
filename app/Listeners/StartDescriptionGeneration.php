<?php

namespace App\Listeners;

use App\Events\ProductScraped;
use App\Jobs\GenerateProductDescription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class StartDescriptionGeneration implements ShouldQueue
{
    use Queueable;

    public function handle(ProductScraped $event): void
    {
        GenerateProductDescription::dispatch($event->scrapingData);
    }
}
