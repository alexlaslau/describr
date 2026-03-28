<?php

namespace App\Services\Translations;

use App\DTOs\TranslationResult;
use App\Exceptions\TranslationFailedException;
use App\Interfaces\TranslationProviderInterface;
use Illuminate\Support\Facades\Http;

class DeepLTranslationService implements TranslationProviderInterface
{
    public function __construct(
        private readonly int $timeout = 20,
    ) {}

    public function translate(string $text, string $targetLanguage): TranslationResult
    {
        try {
            $response = Http::asForm()
                ->timeout($this->timeout)
                ->retry(3, 250, throw: false)
                ->withHeaders([
                    'Authorization' => 'DeepL-Auth-Key ' . config('services.deepl.key'),
                ])
                ->post(config('services.deepl.url'), [
                    'text' => [$text],
                    'target_lang' => strtoupper($targetLanguage),
                    'tag_handling' => 'html',
                    'preserve_formatting' => '1',
                ]);
        } catch (\Throwable $exception) {
            throw new TranslationFailedException('DeepL request failed.', previous: $exception);
        }

        if ($response->failed()) {
            $message = data_get($response->json(), 'message')
                ?? data_get($response->json(), 'detail')
                ?? 'DeepL returned an unsuccessful response.';

            throw new TranslationFailedException($message);
        }

        $translatedText = data_get($response->json(), 'translations.0.text');
        $detectedSourceLanguage = data_get($response->json(), 'translations.0.detected_source_language');

        if (!$translatedText || !$detectedSourceLanguage) {
            throw new TranslationFailedException('Invalid DeepL response.');
        }

        return new TranslationResult(
            text: $translatedText,
            detectedSourceLanguage: $detectedSourceLanguage,
            billedCharacters: data_get($response->json(), 'billed_characters'),
        );
    }
}
