<?php

namespace App\Services;

use App\Models\Product;
use App\Interfaces\AIProviderInterface;
use App\Models\GeneratedDescription;
use App\Exceptions\EmptyScrapedContentException;

class AIProviderService
{
    public function __construct(
        private AIProviderInterface $model,
    ) {}

    public function generate(Product $product): GeneratedDescription
    {
        $product->update(['status' => 'generating']);

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
            \Log::error("[AIProviderService] Description generation failed for product #{$product->id} ({$product->name}): {$e->getMessage()}");

            $product->update(['status' => 'failed']);
            throw $e;
        }
    }

    private function getProductDescription(Product $product): string
    {
        $scrapedContent = $product->getSourcesText();

        if (empty(trim($scrapedContent))) {
            throw new EmptyScrapedContentException();
        }

        $prompt = $this->buildPrompt($product->name, $scrapedContent);

        return $this->model->generate($prompt);
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