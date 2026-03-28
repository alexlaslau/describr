<?php

namespace App\Interfaces;

use App\DTOs\TranslationResult;

interface TranslationProviderInterface
{
    public function providerName(): string;
    public function translate(string $text, string $targetLanguage): TranslationResult;
    public function targetLanguages(): array;
    public function sourceLanguages(): array;
    public function usage(): array;
}
