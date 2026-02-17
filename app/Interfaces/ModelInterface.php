<?php

namespace App\Interfaces;

interface ModelInterface
{
    public function generate(string $prompt): string;
}