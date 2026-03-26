<?php

namespace App\Events;

use App\Models\Product;
use App\Models\GeneratedDescription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DescriptionGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Product $product,
        public readonly GeneratedDescription $description,
    ) {}
}
