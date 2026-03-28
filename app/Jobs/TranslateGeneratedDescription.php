<?php

namespace App\Jobs;

use App\Models\DescriptionTranslation;
use App\Services\DescriptionTranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
}
