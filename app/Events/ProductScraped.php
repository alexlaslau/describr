<?php

namespace App\Events;

use App\DTOs\ProductScrapingData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductScraped
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ProductScrapingData $scrapingData,
    ) {}
}
