<?php

namespace App\Interfaces;

use App\DTOs\ScrapedData;

interface ScraperInterface
{
    public function scrape(string $url): ScrapedData;
}
