<?php

namespace App\Services\Scrapers {
    function getimagesize(string $url): array|false
    {
        // Needed to overwrite PHP's getimagesize function because it would make real HTTP calls to mock urls
        // Return a large enough size so images pass the dimension filter in tests
        return [1000, 1000, IMAGETYPE_JPEG, 'width="1000" height="1000"'];
    }
}

namespace {

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

    describe('image extraction', function () {

        it('extracts images with src and alt', function () {
            $html = '<html><head></head><body>'
                . '<img src="https://example.com/photo1.jpg" alt="Product photo">'
                . '<img src="https://example.com/photo2.png" alt="Another photo">'
                . '</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->images)->toHaveCount(2);
            expect($result->images[0]['src'])->toBe('https://example.com/photo1.jpg');
            expect($result->images[0]['alt'])->toBe('Product photo');
            expect($result->images[1]['src'])->toBe('https://example.com/photo2.png');
        });

        it('resolves relative URLs to absolute', function () {
            $html = '<html><head></head><body>'
                . '<img src="/images/product.jpg" alt="Relative">'
                . '</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->images)->toHaveCount(1);
            expect($result->images[0]['src'])->toBe('https://example.com/images/product.jpg');
        });

        it('uses data-src as fallback for lazy-loaded images', function () {
            $html = '<html><head></head><body>'
                . '<img data-src="https://example.com/lazy.jpg" alt="Lazy image">'
                . '</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->images)->toHaveCount(1);
            expect($result->images[0]['src'])->toBe('https://example.com/lazy.jpg');
        });

        it('filters out icons, logos, pixels, and svgs', function () {
            $html = '<html><head></head><body>'
                . '<img src="https://example.com/icon-cart.png" alt="Icon">'
                . '<img src="https://example.com/site-logo.jpg" alt="Logo">'
                . '<img src="https://example.com/tracking-pixel.png" alt="">'
                . '<img src="https://example.com/graphic.svg" alt="SVG">'
                . '<img src="https://example.com/real-product.jpg" alt="Product">'
                . '</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->images)->toHaveCount(1);
            expect($result->images[0]['src'])->toBe('https://example.com/real-product.jpg');
        });

        it('filters out images smaller than the configured minimum dimension via HTML attributes', function () {
            config(['app.describr.min_image_dimension_pixels' => 300]);

            $html = '<html><head></head><body>'
                . '<img src="https://example.com/tiny.jpg" width="100" height="100" alt="Tiny">'
                . '<img src="https://example.com/small-width.jpg" width="200" height="500" alt="Small W">'
                . '<img src="https://example.com/small-height.jpg" width="500" height="150" alt="Small H">'
                . '<img src="https://example.com/big.jpg" width="800" height="600" alt="Big">'
                . '</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            $srcs = array_column($result->images, 'src');

            expect($srcs)->not->toContain('https://example.com/tiny.jpg');
            expect($srcs)->not->toContain('https://example.com/small-width.jpg');
            expect($srcs)->not->toContain('https://example.com/small-height.jpg');
            expect($srcs)->toContain('https://example.com/big.jpg');
        });

        it('falls back to getimagesize when HTML dimensions are missing', function () {
            // Mock getimagesize returns 1000x1000, so set threshold above that
            config(['app.describr.min_image_dimension_pixels' => 2000]);

            $html = '<html><head></head><body>'
                . '<img src="https://example.com/no-dims.jpg" alt="No dims">'
                . '</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            // Image is 1000x1000 (from mock) but threshold is 2000, so it should be filtered out
            expect($result->images)->toBeEmpty();
        });

        it('only keeps jpg, jpeg, png, and webp formats', function () {
            $html = '<html><head></head><body>'
                . '<img src="https://example.com/photo.jpg" alt="">'
                . '<img src="https://example.com/photo.webp" alt="">'
                . '<img src="https://example.com/photo.gif" alt="">'
                . '<img src="https://example.com/photo.bmp" alt="">'
                . '</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->images)->toHaveCount(2);
            $srcs = array_column($result->images, 'src');
            expect($srcs)->toContain('https://example.com/photo.jpg');
            expect($srcs)->toContain('https://example.com/photo.webp');
        });

        it('deduplicates images by src', function () {
            $html = '<html><head></head><body>'
                . '<img src="https://example.com/same.jpg" alt="First">'
                . '<img src="https://example.com/same.jpg" alt="Duplicate">'
                . '</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->images)->toHaveCount(1);
        });

        it('limits to a maximum of 10 images', function () {
            $imgTags = '';
            for ($i = 1; $i <= 15; $i++) {
                $imgTags .= '<img src="https://example.com/photo' . $i . '.jpg" alt="Photo ' . $i . '">';
            }
            $html = '<html><head></head><body>' . $imgTags . '</body></html>';

            fakeHtmlResponse($html);

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->images)->toHaveCount(10);
        });

        it('returns empty array when no images exist', function () {
            fakeHtmlResponse('<html><head></head><body><p>No images here</p></body></html>');

            $result = $this->scraper->scrape('https://example.com/product');

            expect($result->images)->toBeEmpty();
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

} // end namespace
