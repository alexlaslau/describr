<?php

use App\DTOs\ScrapedData;

describe('ScrapedData', function () {

    describe('toPromptText', function () {

        it('includes the page title', function () {
            $data = new ScrapedData(title: 'Test Product');

            expect($data->toPromptText())->toContain('Page Title: Test Product');
        });

        it('includes meta description', function () {
            $data = new ScrapedData(metaDescription: 'A great product');

            expect($data->toPromptText())->toContain('Meta Description: A great product');
        });

        it('includes OG title when different from page title', function () {
            $data = new ScrapedData(title: 'Page Title', ogTitle: 'OG Title');

            expect($data->toPromptText())->toContain('OG Title: OG Title');
        });

        it('excludes OG title when same as page title', function () {
            $data = new ScrapedData(title: 'Same Title', ogTitle: 'Same Title');

            expect($data->toPromptText())->not->toContain('OG Title:');
        });

        it('includes OG description when different from meta description', function () {
            $data = new ScrapedData(metaDescription: 'Meta desc', ogDescription: 'OG desc');

            expect($data->toPromptText())->toContain('OG Description: OG desc');
        });

        it('excludes OG description when same as meta description', function () {
            $data = new ScrapedData(metaDescription: 'Same desc', ogDescription: 'Same desc');

            expect($data->toPromptText())->not->toContain('OG Description:');
        });

        it('includes structured product data when jsonLd is present', function () {
            $jsonLd = ['@type' => 'Product', 'name' => 'Widget'];
            $data = new ScrapedData(jsonLd: $jsonLd);

            $text = $data->toPromptText();

            expect($text)->toContain('Structured Product Data:');
            expect($text)->toContain('"name": "Widget"');
        });

        it('includes body text as fallback when no jsonLd', function () {
            $data = new ScrapedData(bodyText: 'Some page content here');

            expect($data->toPromptText())->toContain("Body Content:\nSome page content here");
        });

        it('excludes body text when jsonLd is present', function () {
            $jsonLd = ['@type' => 'Product', 'name' => 'Widget'];
            $data = new ScrapedData(jsonLd: $jsonLd, bodyText: 'Some body text');

            expect($data->toPromptText())->not->toContain('Body Content:');
        });

        it('returns empty string when all fields are null', function () {
            $data = new ScrapedData();

            expect($data->toPromptText())->toBe('');
        });

        it('combines all parts with double newlines', function () {
            $data = new ScrapedData(
                title: 'My Product',
                metaDescription: 'Best product ever',
                bodyText: 'Detailed content',
            );

            $text = $data->toPromptText();
            $parts = explode("\n\n", $text);

            expect($parts)->toHaveCount(3);
            expect($parts[0])->toBe('Page Title: My Product');
            expect($parts[1])->toBe('Meta Description: Best product ever');
            expect($parts[2])->toBe("Body Content:\nDetailed content");
        });
    });

    describe('toArray', function () {

        it('returns all fields as an array', function () {
            $jsonLd = ['@type' => 'Product'];
            $data = new ScrapedData(
                title: 'Title',
                metaDescription: 'Meta',
                ogTitle: 'OG Title',
                ogDescription: 'OG Desc',
                ogImage: 'https://example.com/image.jpg',
                jsonLd: $jsonLd,
                bodyText: 'Body',
            );

            expect($data->toArray())->toBe([
                'title' => 'Title',
                'meta_description' => 'Meta',
                'og_title' => 'OG Title',
                'og_description' => 'OG Desc',
                'og_image' => 'https://example.com/image.jpg',
                'json_ld' => $jsonLd,
                'body_text' => 'Body',
                'images' => [],
            ]);
        });

        it('returns nulls for missing fields', function () {
            $data = new ScrapedData();

            $array = $data->toArray();

            expect($array)->toHaveCount(8);
            expect(array_filter($array))->toBeEmpty();
        });
    });
});
