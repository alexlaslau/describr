<?php

use App\Services\Scrapers\DomCrawlerScraper;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->scraper = new DomCrawlerScraper();
});

function fakeHtmlResponse(string $html): void
{
    Http::fake([
        'https://example.com/*' => Http::response($html, 200),
    ]);
}

describe('DomCrawlerScraper', function () {

    describe('title extraction', function () {

        it('extracts the page title', function () {
            fakeHtmlResponse('<html><head><title>My Product</title></head><body>Content</body></html>');

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->title)->toBe('My Product');
        });

        it('returns null when no title tag exists', function () {
            fakeHtmlResponse('<html><head></head><body>Content</body></html>');

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->title)->toBeNull();
        });

        it('trims whitespace from title', function () {
            fakeHtmlResponse('<html><head><title>  Spaced Title  </title></head><body>Content</body></html>');

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->title)->toBe('Spaced Title');
        });
    });

    describe('meta description extraction', function () {

        it('extracts the meta description', function () {
            fakeHtmlResponse('<html><head><meta name="description" content="A nice product"></head><body>Content</body></html>');

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->metaDescription)->toBe('A nice product');
        });

        it('returns null when no meta description exists', function () {
            fakeHtmlResponse('<html><head></head><body>Content</body></html>');

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->metaDescription)->toBeNull();
        });
    });

    describe('OpenGraph extraction', function () {

        it('extracts OG title, description, and image', function () {
            $html = '<html><head>'
                . '<meta property="og:title" content="OG Title">'
                . '<meta property="og:description" content="OG Desc">'
                . '<meta property="og:image" content="https://example.com/img.jpg">'
                . '</head><body>Content</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->ogTitle)->toBe('OG Title');
            expect($result->ogDescription)->toBe('OG Desc');
            expect($result->ogImage)->toBe('https://example.com/img.jpg');
        });

        it('returns null for missing OG tags', function () {
            fakeHtmlResponse('<html><head></head><body>Content</body></html>');

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->ogTitle)->toBeNull();
            expect($result->ogDescription)->toBeNull();
            expect($result->ogImage)->toBeNull();
        });
    });

    describe('JSON-LD extraction', function () {

        it('extracts Product JSON-LD from a direct @type', function () {
            $jsonLd = json_encode(['@type' => 'Product', 'name' => 'Widget', 'brand' => 'Acme']);
            $html = '<html><head><script type="application/ld+json">' . $jsonLd . '</script></head><body>Content</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->jsonLd)->toBe(['@type' => 'Product', 'name' => 'Widget', 'brand' => 'Acme']);
        });

        it('extracts Product JSON-LD from @graph array', function () {
            $jsonLd = json_encode([
                '@graph' => [
                    ['@type' => 'WebSite', 'name' => 'Example'],
                    ['@type' => 'Product', 'name' => 'Widget'],
                ],
            ]);
            $html = '<html><head><script type="application/ld+json">' . $jsonLd . '</script></head><body>Content</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->jsonLd)->toBe(['@type' => 'Product', 'name' => 'Widget']);
        });

        it('returns null when no Product type in JSON-LD', function () {
            $jsonLd = json_encode(['@type' => 'Organization', 'name' => 'Acme Inc']);
            $html = '<html><head><script type="application/ld+json">' . $jsonLd . '</script></head><body>Content</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->jsonLd)->toBeNull();
        });

        it('returns null when JSON-LD is invalid JSON', function () {
            $html = '<html><head><script type="application/ld+json">not valid json</script></head><body>Content</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->jsonLd)->toBeNull();
        });

        it('returns null when no JSON-LD script exists', function () {
            fakeHtmlResponse('<html><head></head><body>Content</body></html>');

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->jsonLd)->toBeNull();
        });
    });

    describe('body text extraction', function () {

        it('extracts body text content', function () {
            fakeHtmlResponse('<html><head></head><body><p>Hello world</p></body></html>');

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->bodyText)->toContain('Hello world');
        });

        it('strips script, style, nav, footer, header, iframe, noscript tags', function () {
            $html = '<html><head></head><body>'
                . '<header>Header</header>'
                . '<nav>Nav</nav>'
                . '<p>Visible content</p>'
                . '<footer>Footer</footer>'
                . '<script>alert("hi")</script>'
                . '<style>.foo{color:red}</style>'
                . '<iframe src="test"></iframe>'
                . '<noscript>No JS</noscript>'
                . '</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->bodyText)->toContain('Visible content');
            expect($result->bodyText)->not->toContain('Header');
            expect($result->bodyText)->not->toContain('Nav');
            expect($result->bodyText)->not->toContain('Footer');
            expect($result->bodyText)->not->toContain('alert');
            expect($result->bodyText)->not->toContain('No JS');
        });

        it('collapses whitespace into single spaces', function () {
            $html = '<html><head></head><body><p>Hello    world</p>   <p>foo</p></body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->bodyText)->not->toMatch('/\s{2,}/');
        });

        it('truncates body text to 1000 words', function () {
            $words = implode(' ', array_fill(0, 1500, 'word'));
            $html = '<html><head></head><body><p>' . $words . '</p></body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect(str_word_count($result->bodyText))->toBeLessThanOrEqual(1001); // 1000 words + "..."
            expect($result->bodyText)->toEndWith('...');
        });

        it('returns empty string when body has no meaningful content', function () {
            fakeHtmlResponse('<html><head></head><body></body></html>');

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->bodyText)->toBeEmpty();
        });
    });

    describe('full scrape integration', function () {

        it('parses a complete product page', function () {
            $jsonLd = json_encode(['@type' => 'Product', 'name' => 'Super Widget']);
            $html = '<html><head>'
                . '<title>Super Widget - Buy Now</title>'
                . '<meta name="description" content="The best widget ever">'
                . '<meta property="og:title" content="Super Widget">'
                . '<meta property="og:description" content="Buy the best widget">'
                . '<meta property="og:image" content="https://example.com/widget.jpg">'
                . '<script type="application/ld+json">' . $jsonLd . '</script>'
                . '</head><body><p>Product details here</p></body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->title)->toBe('Super Widget - Buy Now');
            expect($result->metaDescription)->toBe('The best widget ever');
            expect($result->ogTitle)->toBe('Super Widget');
            expect($result->ogDescription)->toBe('Buy the best widget');
            expect($result->ogImage)->toBe('https://example.com/widget.jpg');
            expect($result->jsonLd)->toBe(['@type' => 'Product', 'name' => 'Super Widget']);
            // Body text should still be extracted even though jsonLd exists
            expect($result->bodyText)->toContain('Product details here');
        });
    });

    describe('HTTP error handling', function () {

        it('throws on HTTP failure', function () {
            Http::fake([
                'https://example.com/*' => Http::response('Server Error', 500),
            ]);

            $this->scraper->scrape('https://example.com/product');
        })->throws(\Illuminate\Http\Client\RequestException::class);
    });
});
