<?php

namespace App\Services\AIProviders;

use InvalidArgumentException;
use App\Interfaces\AIProviderInterface;

class AIProviderFactory
{
    public function make(string $provider): AIProviderInterface
    {
        return match ($provider) {
            'openai' => new OpenAIProvider(),
            'anthropic' => new AnthropicProvider(),
            default => throw new InvalidArgumentException("Unknown AI provider: {$provider}"),
        };
    }
}