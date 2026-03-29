<?php

namespace App\Services\Products;

use App\Interfaces\TranslationProviderInterface;
use App\Models\DescriptionTranslation;

class DescriptionTranslationService
{
    public function __construct(
        private readonly TranslationProviderInterface $translationProvider,
    ) {}

    public function translate(DescriptionTranslation $translation): DescriptionTranslation
    {
        $translation->update([
            'provider' => $this->translationProvider->providerName(),
            'status' => 'processing',
            'error_message' => null,
        ]);

        try {
            $description = $translation->generatedDescription;

            $result = $this->translationProvider->translate(
                text: $description->description,
                targetLanguage: $translation->target_language
            );

            $translation->update([
                'status' => 'completed',
                'source_language' => $result->detectedSourceLanguage,
                'translated_text' => $result->text,
                'billed_characters' => $result->billedCharacters,
                'translated_at' => now(),
            ]);

            return $translation;
        } catch (\Throwable $exception) {
            $translation->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
