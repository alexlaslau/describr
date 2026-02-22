<?php

namespace App\Services;

use App\Models\Product;
use App\Models\GeneratedDescription;
use App\Interfaces\AIProviderInterface;
use App\Exceptions\EmptyScrapedContentException;
use App\Services\AIProviders\AIProviderFactory;
use Illuminate\Support\Facades\Log;

class AIProviderService
{
    private AIProviderInterface $aiProvider;

    public function __construct(
        private AIProviderFactory $factory,
    ) {}

    public function generate(Product $product, string $provider = 'openai'): GeneratedDescription
    {
        $product->update(['status' => 'generating']);

        $this->aiProvider = $this->resolveProvider($provider);

        try {
            $response = $this->getProductDescription($product);

            $description = $product->generatedDescriptions()->create([
                'title' => $product->name,
                'description' => $response,
            ]);

            $product->update([
                'generated_description' => $response,
                'status' => 'completed',
                'generated_at' => now(),
            ]);

            return $description;
        } catch (\Exception $e) {
            Log::error("[AIProviderService] Description generation failed for product #{$product->id} ({$product->name}): {$e->getMessage()}");

            $product->update(['status' => 'failed']);
            throw $e;
        }
    }

    private function resolveProvider(string $provider): AIProviderInterface
    {
        return $this->factory->make($provider);
    }

    private function getProductDescription(Product $product): string
    {
        $scrapedContent = $product->getFullParsedText();

        if (empty(trim($scrapedContent))) {
            throw new EmptyScrapedContentException();
        }

        $prompt = $this->buildPrompt($product->name, $scrapedContent);

        return $this->aiProvider->generate($prompt);
    }

    private function buildPrompt(string $productName, string $scrapedContent): string
    {
        $template = file_get_contents(resource_path('prompts/product-description.txt'));

        return str_replace(
            ['{productName}', '{scrapedContent}'],
            [$productName, $scrapedContent],
            $template,
        );
    }
}