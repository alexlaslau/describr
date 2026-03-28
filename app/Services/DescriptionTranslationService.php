<?php

namespace App\Services;

use App\Interfaces\TranslationProviderInterface;
use App\Models\DescriptionTranslation;
use Illuminate\Support\Facades\Log;

class DescriptionTranslationService
{
    public function __construct(
        private readonly TranslationProviderInterface $translationProvider,
    ) {}

    public function translate(DescriptionTranslation $translation): DescriptionTranslation
    {
        $translation->update([
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
                'translated_at' => now(),
            ]);

            return $translation->fresh();
        } catch (\Throwable $exception) {
            Log::error("[DescriptionTranslationService] Translation failed for translation #{$translation->id}: {$exception->getMessage()}");

            $translation->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
