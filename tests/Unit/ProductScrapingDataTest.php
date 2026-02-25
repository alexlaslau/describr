<?php

use App\DTOs\ProductScrapingData;
use App\Models\Product;

uses(Tests\TestCase::class);

describe('ProductScrapingData', function () {

    describe('fromRequest', function () {

        it('creates a DTO from a full request array', function () {
            $product = Mockery::mock(Product::class);

            $dto = ProductScrapingData::fromRequest($product, [
                'ai_provider' => 'anthropic',
                'target_audience' => 'Tineri 18-30 ani, activi',
                'tone' => 'Entuziast si energic',
                'custom_details' => 'Livrare gratuita',
            ]);

            expect($dto->product)->toBe($product);
            expect($dto->aiProvider)->toBe('anthropic');
            expect($dto->targetAudience)->toBe('Tineri 18-30 ani, activi');
            expect($dto->tone)->toBe('Entuziast si energic');
            expect($dto->customDetails)->toBe('Livrare gratuita');
        });

        it('uses default values for missing keys', function () {
            $product = Mockery::mock(Product::class);

            $dto = ProductScrapingData::fromRequest($product, []);

            expect($dto->aiProvider)->toBe('openai');
            expect($dto->targetAudience)->toBe('General — adulti 25-55 ani');
            expect($dto->tone)->toBe('Prietenos si cald');
            expect($dto->customDetails)->toBe('');
        });

        it('defaults custom_details to empty string when null', function () {
            $product = Mockery::mock(Product::class);

            $dto = ProductScrapingData::fromRequest($product, [
                'ai_provider' => 'openai',
                'target_audience' => 'General — adulti 25-55 ani',
                'tone' => 'Calm si de incredere',
                'custom_details' => null,
            ]);

            expect($dto->customDetails)->toBe('');
        });
    });

    describe('toArray', function () {

        it('returns all settings as a snake_case array', function () {
            $product = Mockery::mock(Product::class);

            $dto = new ProductScrapingData(
                product: $product,
                aiProvider: 'anthropic',
                targetAudience: 'Barbati 30-50 ani, practici',
                tone: 'Urgent si direct',
                customDetails: '1+1 gratis',
            );

            expect($dto->toArray())->toBe([
                'ai_provider' => 'anthropic',
                'target_audience' => 'Barbati 30-50 ani, practici',
                'tone' => 'Urgent si direct',
                'custom_details' => '1+1 gratis',
            ]);
        });

        it('does not include the product in the array', function () {
            $product = Mockery::mock(Product::class);

            $dto = new ProductScrapingData(
                product: $product,
                aiProvider: 'openai',
                targetAudience: 'General — adulti 25-55 ani',
                tone: 'Prietenos si cald',
                customDetails: '',
            );

            expect($dto->toArray())->not->toHaveKey('product');
        });
    });

    describe('immutability', function () {

        it('has readonly properties', function () {
            $product = Mockery::mock(Product::class);

            $dto = new ProductScrapingData(
                product: $product,
                aiProvider: 'openai',
                targetAudience: 'General — adulti 25-55 ani',
                tone: 'Prietenos si cald',
                customDetails: '',
            );

            $reflection = new ReflectionClass($dto);
            foreach ($reflection->getProperties() as $property) {
                expect($property->isReadOnly())->toBeTrue("Property {$property->getName()} should be readonly");
            }
        });
    });
});
