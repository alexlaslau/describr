<?php

use App\Exceptions\TranslationFailedException;
use App\Services\Network\DeepLHttpClient;
use App\Services\Translations\DeepLTranslationService;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

beforeEach(function () {
    config()->set('services.deepl.key', 'deepl-test-key');
    config()->set('services.deepl.base_url', 'https://api-free.deepl.com');

    $this->service = new DeepLTranslationService(new DeepLHttpClient());
});

it('translates text with the Laravel HTTP client', function () {
    Http::fake([
        'https://api-free.deepl.com/v2/translate' => Http::response([
            'translations' => [
                [
                    'detected_source_language' => 'EN',
                    'text' => 'Aceasta este o descriere localizata.',
                    'billed_characters' => 32,
                ],
            ],
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

it('retrieves available target languages', function () {
    Http::fake([
        'https://api-free.deepl.com/v2/languages*' => Http::response([
            ['language' => 'RO', 'name' => 'Romanian', 'supports_formality' => false],
            ['language' => 'DE', 'name' => 'German', 'supports_formality' => true],
        ], 200),
    ]);

    $languages = $this->service->targetLanguages();

    expect($languages)->toHaveCount(2)
        ->and($languages[0]['language'])->toBe('RO')
        ->and($languages[0]['name'])->toBe('Romanian')
        ->and($languages[1]['language'])->toBe('DE')
        ->and($languages[1]['name'])->toBe('German');
});

it('retrieves account usage summary', function () {
    Http::fake([
        'https://api-free.deepl.com/v2/usage' => Http::response([
            'character_count' => 1200,
            'character_limit' => 500000,
        ], 200),
    ]);

    $usage = $this->service->usage();

    expect($usage)->toBe([
        'character_count' => 1200,
        'character_limit' => 500000,
    ]);
});

it('keeps billed characters from the DeepL response', function () {
    Http::fake([
        'https://api-free.deepl.com/v2/translate' => Http::response([
            'translations' => [
                [
                    'detected_source_language' => 'EN',
                    'text' => 'Hallo Welt',
                    'billed_characters' => 11,
                ],
            ],
        ], 200),
    ]);

    $result = $this->service->translate('Hello world', 'DE');

    expect($result->billedCharacters)->toBe(11);
});

it('throws a domain exception when DeepL returns an invalid payload', function () {
    Http::fake([
        'https://api-free.deepl.com/v2/translate' => Http::response([
            'translations' => [],
        ], 200),
    ]);

    $this->service->translate('Hello', 'DE');
})->throws(TranslationFailedException::class, 'Missing DeepL response information.');

it('throws a domain exception when DeepL returns an error response', function () {
    Http::fake([
        'https://api-free.deepl.com/v2/translate' => Http::response([
            'message' => 'Authorization failed',
        ], 403),
    ]);

    $this->service->translate('Hello', 'DE');
})->throws(TranslationFailedException::class, 'Authorization failed');
