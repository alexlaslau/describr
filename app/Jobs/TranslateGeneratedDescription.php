<?php

namespace App\Jobs;

use App\Models\DescriptionTranslation;
use App\Services\DescriptionTranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class TranslateGeneratedDescription implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [60, 180];
    public int $timeout = 120;

    public function __construct(
        public readonly DescriptionTranslation $translation,
    ) {}

    public function handle(DescriptionTranslationService $translationService): void
    {
        $translationService->translate($this->translation);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[TranslateGeneratedDescription] Translation failed.', [
            'translation_id' => $this->translation->id,
            'generated_description_id' => $this->translation->generated_description_id,
            'target_language' => $this->translation->target_language,
            'provider' => $this->translation->provider,
            'error' => $exception->getMessage(),
        ]);
    }

    public function tags(): array
    {
        return [
            'product:'.$this->translation->productId(),
            'translation:'.$this->translation->id,
            'language:'.$this->translation->target_language,
            'provider:'.$this->translation->provider,
            'pipeline:translate',
        ];
    }
}
