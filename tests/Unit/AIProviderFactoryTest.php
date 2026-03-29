<?php

use App\Services\Integrations\AI\AIProviderFactory;
use App\Services\Integrations\AI\AnthropicProvider;
use App\Services\Integrations\AI\OpenAIProvider;

describe('AIProviderFactory', function () {

    beforeEach(function () {
        $this->factory = new AIProviderFactory;
    });

    it('returns an OpenAIProvider for openai', function () {
        $provider = $this->factory->make('openai');

        expect($provider)->toBeInstanceOf(OpenAIProvider::class);
    });

    it('returns an AnthropicProvider for anthropic', function () {
        $provider = $this->factory->make('anthropic');

        expect($provider)->toBeInstanceOf(AnthropicProvider::class);
    });

    it('throws InvalidArgumentException for an unknown provider', function () {
        $this->factory->make('gemini');
    })->throws(InvalidArgumentException::class, 'Unknown AI provider: gemini');
});
