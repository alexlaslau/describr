<?php

use App\Services\AIProviderService;
use App\Services\AIProviders\AIProviderFactory;
use App\DTOs\ProductScrapingData;
use App\Interfaces\AIProviderInterface;
use App\Models\Product;
use App\Models\GeneratedDescription;
use App\Exceptions\EmptyScrapedContentException;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->mockModel = Mockery::mock(AIProviderInterface::class);

    $mockFactory = Mockery::mock(AIProviderFactory::class);
    $mockFactory->shouldReceive('make')->andReturn($this->mockModel);

    $this->service = new AIProviderService($mockFactory);
});

function makeProduct(string $name, int $id, string $scrapedText): Product
{
    $product = Mockery::mock(Product::class)->makePartial();
    $product->name = $name;
    $product->id = $id;
    $product->shouldReceive('getFullParsedText')->andReturn($scrapedText);
    $product->shouldReceive('update')->andReturn(true);

    return $product;
}

function makeScrapingData(Product $product, array $overrides = []): ProductScrapingData
{
    return new ProductScrapingData(
        product: $product,
        aiProvider: $overrides['aiProvider'] ?? 'openai',
        targetAudience: $overrides['targetAudience'] ?? 'General — adulti 25-55 ani',
        tone: $overrides['tone'] ?? 'Prietenos si cald',
        customDetails: $overrides['customDetails'] ?? '',
    );
}

describe('AIProviderService', function () {

    describe('prompt building', function () {

        it('builds prompt with product name and scraped content from template', function () {
            $product = makeProduct('Super Widget', 1, 'Scraped data about the widget');
            $product->shouldReceive('generatedDescriptions->create')->andReturn(new GeneratedDescription());

            $this->mockModel->shouldReceive('generate')
                ->once()
                ->withArgs(function (string $prompt) {
                    return str_contains($prompt, 'Super Widget')
                        && str_contains($prompt, 'Scraped data about the widget');
                })
                ->andReturn('Generated description text');

            $this->service->generate(makeScrapingData($product));
        });

        it('injects target audience and tone into the prompt', function () {
            $product = makeProduct('Settings Test', 10, 'Some content');
            $product->shouldReceive('generatedDescriptions->create')->andReturn(new GeneratedDescription());

            $this->mockModel->shouldReceive('generate')
                ->once()
                ->withArgs(function (string $prompt) {
                    return str_contains($prompt, 'Tineri 18-30 ani, activi')
                        && str_contains($prompt, 'Entuziast si energic');
                })
                ->andReturn('Short description');

            $this->service->generate(makeScrapingData($product, [
                'targetAudience' => 'Tineri 18-30 ani, activi',
                'tone' => 'Entuziast si energic',
            ]));
        });
    });

    describe('empty content handling', function () {

        it('throws EmptyScrapedContentException when scraped content is empty', function () {
            $product = makeProduct('Empty Product', 2, '');
            $this->service->generate(makeScrapingData($product));
        })->throws(EmptyScrapedContentException::class);

        it('throws EmptyScrapedContentException when scraped content is only whitespace', function () {
            $product = makeProduct('Whitespace Product', 3, '   ');
            $this->service->generate(makeScrapingData($product));
        })->throws(EmptyScrapedContentException::class);
    });

    describe('status transitions', function () {

        it('sets status to generating, then completed on success', function () {
            $statusUpdates = [];

            $product = Mockery::mock(Product::class)->makePartial();
            $product->name = 'Widget';
            $product->id = 4;
            $product->shouldReceive('getFullParsedText')->andReturn('Some content');
            $product->shouldReceive('update')
                ->andReturnUsing(function ($data) use (&$statusUpdates) {
                    if (isset($data['status'])) {
                        $statusUpdates[] = $data['status'];
                    }
                    return true;
                });

            $product->shouldReceive('generatedDescriptions->create')->andReturn(new GeneratedDescription());

            $this->mockModel->shouldReceive('generate')->andReturn('A description');

            $this->service->generate(makeScrapingData($product));

            expect($statusUpdates)->toBe(['generating', 'completed']);
        });

        it('sets status to generating, then failed on AI error', function () {
            $statusUpdates = [];

            $product = Mockery::mock(Product::class)->makePartial();
            $product->name = 'Failing Widget';
            $product->id = 5;
            $product->shouldReceive('getFullParsedText')->andReturn('Some content');
            $product->shouldReceive('update')
                ->andReturnUsing(function ($data) use (&$statusUpdates) {
                    if (isset($data['status'])) {
                        $statusUpdates[] = $data['status'];
                    }
                    return true;
                });

            $this->mockModel->shouldReceive('generate')->andThrow(new \RuntimeException('API error'));

            try {
                $this->service->generate(makeScrapingData($product));
            } catch (\RuntimeException) {
                // expected
            }

            expect($statusUpdates)->toBe(['generating', 'failed']);
        });
    });

    describe('return value', function () {

        it('returns a GeneratedDescription instance on success', function () {
            $description = new GeneratedDescription();
            $description->title = 'Widget';
            $description->description = 'A nice widget';

            $product = makeProduct('Widget', 6, 'Content');
            $product->shouldReceive('generatedDescriptions->create')->andReturn($description);

            $this->mockModel->shouldReceive('generate')->andReturn('A nice widget');

            $result = $this->service->generate(makeScrapingData($product));

            expect($result)->toBeInstanceOf(GeneratedDescription::class);
        });

        it('returns a GeneratedDescription with the correct title and description', function () {
            $description = new GeneratedDescription();
            $description->title = 'Gadget';
            $description->description = 'An amazing gadget';

            $product = makeProduct('Gadget', 7, 'Gadget info');
            $product->shouldReceive('generatedDescriptions->create')->andReturn($description);

            $this->mockModel->shouldReceive('generate')->andReturn('An amazing gadget');

            $result = $this->service->generate(makeScrapingData($product));

            expect($result->title)->toBe('Gadget');
            expect($result->description)->toBe('An amazing gadget');
        });
    });

    describe('provider resolution', function () {

        it('forwards the provider string to the factory', function () {
            $mockModel = Mockery::mock(AIProviderInterface::class);
            $mockModel->shouldReceive('generate')->andReturn('Result');

            $mockFactory = Mockery::mock(AIProviderFactory::class);
            $mockFactory->shouldReceive('make')
                ->once()
                ->with('anthropic')
                ->andReturn($mockModel);

            $service = new AIProviderService($mockFactory);

            $product = makeProduct('Test', 8, 'Content');
            $product->shouldReceive('generatedDescriptions->create')->andReturn(new GeneratedDescription());

            $service->generate(makeScrapingData($product, ['aiProvider' => 'anthropic']));
        });

        it('defaults to openai when no provider is specified', function () {
            $mockModel = Mockery::mock(AIProviderInterface::class);
            $mockModel->shouldReceive('generate')->andReturn('Result');

            $mockFactory = Mockery::mock(AIProviderFactory::class);
            $mockFactory->shouldReceive('make')
                ->once()
                ->with('openai')
                ->andReturn($mockModel);

            $service = new AIProviderService($mockFactory);

            $product = makeProduct('Test', 9, 'Content');
            $product->shouldReceive('generatedDescriptions->create')->andReturn(new GeneratedDescription());

            $service->generate(makeScrapingData($product));
        });
    });
});
