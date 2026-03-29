<?php

namespace App\Interfaces;

interface AIProviderInterface
{
    public function generate(string $prompt): string;
}
