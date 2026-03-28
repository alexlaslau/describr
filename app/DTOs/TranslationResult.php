<?php

namespace App\DTOs;

class TranslationResult
{
    public function __construct(
        public readonly string $text,
        public readonly string $detectedSourceLanguage,
        public readonly ?int $billedCharacters = null,
    ) {}

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'detected_source_language' => $this->detectedSourceLanguage,
            'billed_characters' => $this->billedCharacters,
        ];
    }
}
