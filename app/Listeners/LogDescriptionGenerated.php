<?php

namespace App\Listeners;

use App\Events\DescriptionGenerated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class LogDescriptionGenerated implements ShouldQueue
{
    use Queueable;

    public function handle(DescriptionGenerated $event): void
    {
        Log::info("[DescriptionGenerated] Product #{$event->product->id} ({$event->product->name}) — description generated successfully.");
    }
}
