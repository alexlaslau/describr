<?php

namespace App\Services\Models;

use App\Interfaces\ModelInterface;
use Illuminate\Support\Facades\Http;

class OpenAIModel implements ModelInterface
{
    public function __construct(
        private string $model = 'gpt-4o-mini',
        private int $maxTokens = 4096,
        private int $timeout = 180,
    ) {}

    public function generate(string $prompt): string
    {
        \Log::info($prompt);
        $response = Http::timeout($this->timeout)
            ->retry(3, 200)
            ->withToken(config('services.openai.key'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => $this->model,
                'input' => $prompt,
                'max_output_tokens' => $this->maxTokens,
            ]);

        $response->throw();

        $generatedText = data_get($response->json(), 'output.0.content.0.text');

        if (!$generatedText) {
            throw new \RuntimeException('Invalid OpenAI response.');
        }

        return $generatedText;
    }

}
