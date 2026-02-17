<?php

namespace App\DTOs;

class ScrapedData
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $metaDescription = null,
        public readonly ?string $ogTitle = null,
        public readonly ?string $ogDescription = null,
        public readonly ?string $ogImage = null,
        public readonly ?array $jsonLd = null,
        public readonly ?string $bodyText = null,
        public readonly ?string $rawHtml = null,
    ) {}

    public function toPromptText(): string
    {
        $parts = [];

        if ($this->title) {
            $parts[] = "Page Title: {$this->title}";
        }

        if ($this->metaDescription) {
            $parts[] = "Meta Description: {$this->metaDescription}";
        }

        if ($this->ogTitle && $this->ogTitle !== $this->title) {
            $parts[] = "OG Title: {$this->ogTitle}";
        }

        if ($this->ogDescription && $this->ogDescription !== $this->metaDescription) {
            $parts[] = "OG Description: {$this->ogDescription}";
        }

        if ($this->jsonLd) {
            $parts[] = "Structured Data:\n" . json_encode($this->jsonLd, JSON_PRETTY_PRINT);
        }

        if ($this->bodyText) {
            $parts[] = "Body Content:\n{$this->bodyText}";
        }

        return implode("\n\n", $parts);
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'meta_description' => $this->metaDescription,
            'og_title' => $this->ogTitle,
            'og_description' => $this->ogDescription,
            'og_image' => $this->ogImage,
            'json_ld' => $this->jsonLd,
            'body_text' => $this->bodyText,
        ];
    }
}