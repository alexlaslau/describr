<?php

namespace App\Services\Integrations\AI;

use App\DTOs\ProductScrapingData;
use App\Events\DescriptionFailed;
use App\Events\DescriptionGenerated;
use App\Exceptions\EmptyScrapedContentException;
use App\Interfaces\AIProviderInterface;
use App\Models\GeneratedDescription;

class AIProviderService
{
    private AIProviderInterface $aiProvider;

    public function __construct(
        private AIProviderFactory $factory,
    ) {}

    public function generate(ProductScrapingData $scrapingData): GeneratedDescription
    {
        $product = $scrapingData->product;

        $product->update(['status' => 'generating']);

        $this->aiProvider = $this->resolveProvider($scrapingData->aiProvider);

        try {
            $scrapedContent = $product->getFullParsedText();

            if (empty(trim($scrapedContent))) {
                throw new EmptyScrapedContentException;
            }

            $prompt = $this->buildPrompt($product->name, $scrapedContent, $scrapingData);

            $response = $this->aiProvider->generate($prompt);

            $description = $product->generatedDescriptions()->create([
                'title' => $product->name,
                'description' => $response,
                'prompt_settings' => $scrapingData->toArray(),
            ]);

            $product->update([
                'generated_description' => $response,
                'status' => 'completed',
                'generated_at' => now(),
            ]);

            DescriptionGenerated::dispatch($product, $description);

            return $description;
        } catch (\Exception $e) {
            $product->update(['status' => 'failed']);

            DescriptionFailed::dispatch($product, $e);

            throw $e;
        }
    }

    private function resolveProvider(string $provider): AIProviderInterface
    {
        return $this->factory->make($provider);
    }

    private function buildPrompt(string $productName, string $scrapedContent, ProductScrapingData $scrapingData): string
    {
        $template = file_get_contents(resource_path('prompts/product-description.txt'));

        return str_replace(
            ['{productName}', '{scrapedContent}', '{targetAudience}', '{tone}', '{customDetails}'],
            [$productName, $scrapedContent, $scrapingData->targetAudience, $scrapingData->tone, $scrapingData->customDetails],
            $template,
        );
    }
}
