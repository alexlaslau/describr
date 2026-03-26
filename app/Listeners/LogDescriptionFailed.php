<?php

namespace App\Listeners;

use App\Events\DescriptionFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class LogDescriptionFailed implements ShouldQueue
{
    use Queueable;

    public function handle(DescriptionFailed $event): void
    {
        Log::error("[DescriptionFailed] Product #{$event->product->id} ({$event->product->name}): {$event->exception->getMessage()}");
    }
}
