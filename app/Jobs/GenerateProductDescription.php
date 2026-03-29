<?php

namespace App\Jobs;

use App\DTOs\ProductScrapingData;
use App\Services\Integrations\AI\AIProviderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateProductDescription implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [100, 200];

    public int $timeout = 180;

    public function __construct(
        private ProductScrapingData $scrapingData,
    ) {}

    public function handle(AIProviderService $aiProviderService): void
    {
        $aiProviderService->generate($this->scrapingData);
    }

    public function failed(\Throwable $e): void
    {
        $this->scrapingData->product->update(['status' => 'failed']);
    }

    public function tags(): array
    {
        return [
            'product:' . $this->scrapingData->product->id,
            'provider:' . $this->scrapingData->aiProvider,
            'pipeline:generate',
        ];
    }
}
