<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\AIProviderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateProductDescription implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [100, 200];
    public int $timeout = 180;

    public function __construct(
        private Product $product,
        private string $provider = 'openai',
    ) {}

    public function handle(AIProviderService $aiProviderService): void
    {
        $aiProviderService->generate($this->product, $this->provider);
    }

    public function failed(\Throwable $e): void
    {
        $this->product->update(['status' => 'failed']);
    }
}