<?php

namespace App\Services\Scrapers;

use App\DTOs\ScrapedData;
use App\Interfaces\ScraperInterface;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class DomCrawlerScraper implements ScraperInterface
{
    private Crawler $crawler;

    public function scrape(string $url): ScrapedData
    {
        $html = $this->fetchHtml($url);

        return $this->parse($html);
    }

    private function fetchHtml(string $url): string
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ])
            ->get($url);

        $response->throw();

        return $response->body();
    }

    private function parse(string $html): ScrapedData
    {
        $this->crawler = new Crawler($html);

        return new ScrapedData(
            title: $this->extractTitle(),
            metaDescription: $this->extractMeta('description'),
            ogTitle: $this->extractOg('og:title'),
            ogDescription: $this->extractOg('og:description'),
            ogImage: $this->extractOg('og:image'),
            jsonLd: $this->extractJsonLd(),
            bodyText: $this->extractBodyText()
        );
    }

    private function extractTitle(): ?string
    {
        return $this->crawler->filter('title')->count()
            ? trim($this->crawler->filter('title')->text())
            : null;
    }

    private function extractMeta(string $name): ?string
    {
        $node = $this->crawler->filter("meta[name=\"{$name}\"]");

        return $node->count() ? $node->attr('content') : null;
    }

    private function extractOg(string $property): ?string
    {
        $node = $this->crawler->filter("meta[property=\"{$property}\"]");

        return $node->count() ? $node->attr('content') : null;
    }

    private function extractJsonLd(): ?array
    {
        $jsonLd = null;

        $this->crawler->filter('script[type="application/ld+json"]')->each(function (Crawler $node) use (&$jsonLd) {
            $data = json_decode($node->text(), true);

            if (!$data) {
                return;
            }

            if (isset($data['@type']) && $data['@type'] === 'Product') {
                $jsonLd = $data;
                return;
            }
            if (isset($data['@graph']) && is_array($data['@graph'])) {
                foreach ($data['@graph'] as $item) {
                    if (isset($item['@type']) && $item['@type'] === 'Product') {
                        $jsonLd = $item;
                        return;
                    }
                }
            }
        });

        return $jsonLd;
    }

    private function extractBodyText(): ?string
    {
        if (!$this->crawler->filter('body')->count()) {
            return null;
        }

        $this->crawler->filter('script, style, nav, footer, header, iframe, noscript')->each(function (Crawler $node) {
            $domNode = $node->getNode(0);
            $domNode->parentNode->removeChild($domNode);
        });

        $text = $this->crawler->filter('body')->text();

        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        $words = str_word_count($text);
        if ($words > 1000) {
            $text = implode(' ', array_slice(explode(' ', $text), 0, 1000)) . '...';
        }

        return $text;
    }
}