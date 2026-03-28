<?php

namespace App\Services\Translations;

use App\DTOs\TranslationResult;
use App\Exceptions\DeepLApiException;
use App\Interfaces\TranslationProviderInterface;
use App\Enums\DeepLEndpoint;
use App\Enums\HttpMethod;
use App\Services\Network\DeepLHttpClient;

class DeepLTranslationService implements TranslationProviderInterface
{
    public function __construct(
        private readonly DeepLHttpClient $httpClient,
    ) {}

    public function translate(string $text, string $targetLanguage): TranslationResult
    {
        $response = $this->httpClient->request(
            endpoint: DeepLEndpoint::TRANSLATE_TEXT,
            method: HttpMethod::POST,
            data: [
                'text' => [$text],
                'target_lang' => strtoupper($targetLanguage),
                'show_billed_characters' => true,
                'preserve_formatting' => true,
            ],
        );

        $translatedText = data_get($response, 'translations.0.text');
        $detectedSourceLanguage = data_get($response, 'translations.0.detected_source_language');
        $billedCharacters = data_get($response, 'translations.0.billed_characters');

        if (!$translatedText || !$detectedSourceLanguage || !$billedCharacters) {
            throw new DeepLApiException('Missing DeepL response information.');
        }

        return new TranslationResult(
            text: $translatedText,
            detectedSourceLanguage: $detectedSourceLanguage,
            billedCharacters: $billedCharacters,
        );
    }

    public function sourceLanguages(): array
    {
        return $this->languages('source');
    }

    public function targetLanguages(): array
    {
        return $this->languages('target');
    }

    public function usage(): array
    {
        $response = $this->httpClient->request(
            endpoint: DeepLEndpoint::USAGE,
            method: HttpMethod::GET,
        );

        return [
            'character_count' => data_get($response, 'character_count', 0),
            'character_limit' => data_get($response, 'character_limit', 0),
        ];
    }

    private function languages(string $type): array
    {
        return $this->httpClient->request(
            endpoint: DeepLEndpoint::LANGUAGES,
            method: HttpMethod::GET,
            data: ['type' => $type],
        );
    }
}
