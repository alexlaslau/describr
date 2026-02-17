<?php

namespace App\Interfaces;

interface ScraperInterface
{
    public function fetchHtml(string $url): string;
    public function parse(string $html): ScrapedData;
    public function scrape(string $url): ScrapedData;
}