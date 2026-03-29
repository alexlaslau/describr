<?php

namespace App\Services\Integrations\AI;

use App\Interfaces\AIProviderInterface;
use InvalidArgumentException;

class AIProviderFactory
{
    public function make(string $provider): AIProviderInterface
    {
        return match ($provider) {
            'openai' => new OpenAIProvider,
            'anthropic' => new AnthropicProvider,
            default => throw new InvalidArgumentException("Unknown AI provider: {$provider}"),
        };
    }
}
