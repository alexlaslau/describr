<?php

use App\Jobs\ScrapeProduct;
use App\Models\ApiClient;
use App\Models\User;
use App\Services\HmacSignatureService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('app.describr.api_hmac_ttl_seconds', 300);
    $this->signatureService = app(HmacSignatureService::class);
});

it('creates a product for the authenticated api client user', function () {
    Queue::fake();

    $user = User::factory()->create();
    $apiClient = ApiClient::factory()->for($user)->create([
        'client_id' => 'client-create',
        'client_secret' => 'secret-create',
    ]);

    $payload = [
        'name' => 'API Created Product',
        'links' => [
            'https://example.com/product?ref=1',
            'https://example.com/product?ref=1',
            'https://example.com/second-product',
        ],
        'ai_provider' => 'openai',
        'target_audience' => 'General — adulti 25-55 ani',
        'tone' => 'Prietenos si cald',
        'custom_details' => 'API created',
    ];

    $timestamp = (string) now()->timestamp;
    $path = 'api/products';
    $signature = $this->signatureService->signatureFor('POST', $path, $timestamp, $apiClient->client_secret);

    $this->postJson("/{$path}", $payload, [
        'X-Describr-Client' => $apiClient->client_id,
        'X-Describr-Timestamp' => $timestamp,
        'X-Describr-Signature' => $signature,
    ])->assertCreated()
        ->assertJsonPath('data.name', 'API Created Product')
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.links_count', 2);

    $this->assertDatabaseHas('products', [
        'user_id' => $user->id,
        'name' => 'API Created Product',
        'status' => 'pending',
    ]);

    Queue::assertPushed(ScrapeProduct::class);
});

it('validates product creation payloads', function () {
    $user = User::factory()->create();
    $apiClient = ApiClient::factory()->for($user)->create([
        'client_secret' => 'secret-invalid',
    ]);

    $timestamp = (string) now()->timestamp;
    $path = 'api/products';
    $signature = $this->signatureService->signatureFor('POST', $path, $timestamp, $apiClient->client_secret);

    $this->postJson("/{$path}", [
        'name' => '',
        'links' => [],
    ], [
        'X-Describr-Client' => $apiClient->client_id,
        'X-Describr-Timestamp' => $timestamp,
        'X-Describr-Signature' => $signature,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'links', 'ai_provider', 'target_audience', 'tone']);
});
