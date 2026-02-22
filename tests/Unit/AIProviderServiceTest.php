<?php

use App\Services\AIProviderService;
use App\Services\AIProviders\AIProviderFactory;
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

describe('AIProviderService', function () {

    describe('prompt building', function () {

        it('builds prompt with product name and scraped content from template', function () {
            $product = Mockery::mock(Product::class)->makePartial();
            $product->name = 'Super Widget';
            $product->id = 1;
            $product->shouldReceive('getFullParsedText')->andReturn('Scraped data about the widget');
            $product->shouldReceive('update')->andReturn(true);
            $product->shouldReceive('generatedDescriptions->create')->andReturn(new GeneratedDescription());

            $this->mockModel->shouldReceive('generate')
                ->once()
                ->withArgs(function (string $prompt) {
                    return str_contains($prompt, 'Super Widget')
                        && str_contains($prompt, 'Scraped data about the widget');
                })
                ->andReturn('Generated description text');

            $this->service->generate($product);
        });
    });

    describe('empty content handling', function () {

        it('throws EmptyScrapedContentException when scraped content is empty', function () {
            $product = Mockery::mock(Product::class)->makePartial();
            $product->name = 'Empty Product';
            $product->id = 2;
            $product->shouldReceive('getFullParsedText')->andReturn('');
            $product->shouldReceive('update')->andReturn(true);

            $this->service->generate($product);
        })->throws(EmptyScrapedContentException::class);

        it('throws EmptyScrapedContentException when scraped content is only whitespace', function () {
            $product = Mockery::mock(Product::class)->makePartial();
            $product->name = 'Whitespace Product';
            $product->id = 3;
            $product->shouldReceive('getFullParsedText')->andReturn('   ');
            $product->shouldReceive('update')->andReturn(true);

            $this->service->generate($product);
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

            $this->service->generate($product);

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
                $this->service->generate($product);
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

            $product = Mockery::mock(Product::class)->makePartial();
            $product->name = 'Widget';
            $product->id = 6;
            $product->shouldReceive('getFullParsedText')->andReturn('Content');
            $product->shouldReceive('update')->andReturn(true);
            $product->shouldReceive('generatedDescriptions->create')->andReturn($description);

            $this->mockModel->shouldReceive('generate')->andReturn('A nice widget');

            $result = $this->service->generate($product);

            expect($result)->toBeInstanceOf(GeneratedDescription::class);
        });

        it('returns a GeneratedDescription with the correct title and description', function () {
            $description = new GeneratedDescription();
            $description->title = 'Gadget';
            $description->description = 'An amazing gadget';

            $product = Mockery::mock(Product::class)->makePartial();
            $product->name = 'Gadget';
            $product->id = 7;
            $product->shouldReceive('getFullParsedText')->andReturn('Gadget info');
            $product->shouldReceive('update')->andReturn(true);
            $product->shouldReceive('generatedDescriptions->create')->andReturn($description);

            $this->mockModel->shouldReceive('generate')->andReturn('An amazing gadget');

            $result = $this->service->generate($product);

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

            $product = Mockery::mock(Product::class)->makePartial();
            $product->name = 'Test';
            $product->id = 8;
            $product->shouldReceive('getFullParsedText')->andReturn('Content');
            $product->shouldReceive('update')->andReturn(true);
            $product->shouldReceive('generatedDescriptions->create')->andReturn(new GeneratedDescription());

            $service->generate($product, 'anthropic');
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

            $product = Mockery::mock(Product::class)->makePartial();
            $product->name = 'Test';
            $product->id = 9;
            $product->shouldReceive('getFullParsedText')->andReturn('Content');
            $product->shouldReceive('update')->andReturn(true);
            $product->shouldReceive('generatedDescriptions->create')->andReturn(new GeneratedDescription());

            $service->generate($product);
        });
    });
});
