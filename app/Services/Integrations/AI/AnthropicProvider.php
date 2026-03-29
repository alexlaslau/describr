<?php

namespace App\Services\Integrations\AI;

use App\Interfaces\AIProviderInterface;
use Illuminate\Support\Facades\Http;

class AnthropicProvider implements AIProviderInterface
{
    public function __construct(
        private string $model = 'claude-haiku-4-5-20251001',
        private int $maxTokens = 4096,
        private int $timeout = 180,
    ) {}

    public function generate(string $prompt): string
    {
        $response = Http::timeout($this->timeout)
            ->retry(3, 200)
            ->withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        $response->throw();

        $generatedText = data_get($response->json(), 'content.0.text');

        if (!$generatedText) {
            throw new \RuntimeException('Invalid Anthropic response.');
        }

        return $generatedText;
    }
}
