<?php

use App\Exceptions\TranslationFailedException;
use App\Services\Translations\DeepLTranslationService;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

beforeEach(function () {
    config()->set('services.deepl.key', 'deepl-test-key');
    config()->set('services.deepl.url', 'https://api-free.deepl.com/v2/translate');

    $this->service = new DeepLTranslationService();
});

it('translates text with the Laravel HTTP client', function () {
    Http::fake([
        'https://api-free.deepl.com/v2/translate' => Http::response([
            'translations' => [
                [
                    'detected_source_language' => 'EN',
                    'text' => 'Aceasta este o descriere localizata.',
                ],
            ],
            'billed_characters' => 32,
        ], 200),
    ]);

    $result = $this->service->translate('This is a localized description.', 'RO');

    expect($result->text)->toBe('Aceasta este o descriere localizata.')
        ->and($result->detectedSourceLanguage)->toBe('EN')
        ->and($result->billedCharacters)->toBe(32);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api-free.deepl.com/v2/translate'
            && $request->hasHeader('Authorization', 'DeepL-Auth-Key deepl-test-key')
            && $request['target_lang'] === 'RO';
    });
});

it('throws a domain exception when DeepL returns an invalid payload', function () {
    Http::fake([
        'https://api-free.deepl.com/v2/translate' => Http::response([
            'translations' => [],
        ], 200),
    ]);

    $this->service->translate('Hello', 'DE');
})->throws(TranslationFailedException::class, 'Invalid DeepL response.');

it('throws a domain exception when DeepL returns an error response', function () {
    Http::fake([
        'https://api-free.deepl.com/v2/translate' => Http::response([
            'message' => 'Authorization failed',
        ], 403),
    ]);

    $this->service->translate('Hello', 'DE');
})->throws(TranslationFailedException::class, 'Authorization failed');
