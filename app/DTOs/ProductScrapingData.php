<?php

namespace App\DTOs;

use App\Models\Product;

class ProductScrapingData
{
    public function __construct(
        public readonly Product $product,
        public readonly string $aiProvider,
        public readonly string $targetAudience,
        public readonly string $tone,
        public readonly string $customDetails,
    ) {}

    public static function fromRequest(Product $product, array $data): self
    {
        return new self(
            product: $product,
            aiProvider: $data['ai_provider'] ?? 'openai',
            targetAudience: $data['target_audience'] ?? 'General — adulti 25-55 ani',
            tone: $data['tone'] ?? 'Prietenos si cald',
            customDetails: $data['custom_details'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'ai_provider' => $this->aiProvider,
            'target_audience' => $this->targetAudience,
            'tone' => $this->tone,
            'custom_details' => $this->customDetails,
        ];
    }
}
