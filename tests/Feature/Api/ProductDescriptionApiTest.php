<?php

use App\Models\ApiClient;
use App\Models\GeneratedDescription;
use App\Models\Product;
use App\Models\User;
use App\Services\Security\HmacSignatureService;

beforeEach(function () {
    config()->set('app.describr.api_hmac_ttl_seconds', 300);
    $this->signatureService = app(HmacSignatureService::class);
});

it('returns a product description with a valid hmac signature', function () {
    $user = User::factory()->create();
    $apiClient = ApiClient::factory()->for($user)->create([
        'client_id' => 'client-demo',
        'client_secret' => 'secret-demo',
    ]);
    $product = Product::factory()->completed()->for($user)->create([
        'generated_description' => 'Primary generated description.',
    ]);

    GeneratedDescription::factory()->for($product)->create([
        'description' => 'Primary generated description.',
    ]);

    $timestamp = (string) now()->timestamp;
    $path = "api/products/{$product->id}/description";
    $signature = $this->signatureService->signatureFor('GET', $path, $timestamp, $apiClient->client_secret);

    $this->getJson("/{$path}", [
        'X-Describr-Client' => $apiClient->client_id,
        'X-Describr-Timestamp' => $timestamp,
        'X-Describr-Signature' => $signature,
    ])->assertOk()
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.description', 'Primary generated description.')
        ->assertJsonMissingPath('data.generated_descriptions');
});

it('rejects requests with an invalid signature', function () {
    $user = User::factory()->create();
    $apiClient = ApiClient::factory()->for($user)->create();
    $product = Product::factory()->completed()->for($user)->create();

    $this->getJson("/api/products/{$product->id}/description", [
        'X-Describr-Client' => $apiClient->client_id,
        'X-Describr-Timestamp' => (string) now()->timestamp,
        'X-Describr-Signature' => 'invalid-signature',
    ])->assertUnauthorized()
        ->assertJsonPath('message', 'Invalid HMAC signature.');
});

it('rejects expired signatures', function () {
    $user = User::factory()->create();
    $apiClient = ApiClient::factory()->for($user)->create([
        'client_secret' => 'stale-secret',
    ]);
    $product = Product::factory()->completed()->for($user)->create();

    $timestamp = (string) now()->subMinutes(10)->timestamp;
    $path = "api/products/{$product->id}/description";
    $signature = $this->signatureService->signatureFor('GET', $path, $timestamp, $apiClient->client_secret);

    $this->getJson("/{$path}", [
        'X-Describr-Client' => $apiClient->client_id,
        'X-Describr-Timestamp' => $timestamp,
        'X-Describr-Signature' => $signature,
    ])->assertUnauthorized()
        ->assertJsonPath('message', 'Expired HMAC signature.');
});

it('returns not found when the product has no generated description', function () {
    $user = User::factory()->create();
    $apiClient = ApiClient::factory()->for($user)->create([
        'client_secret' => 'secret-missing-description',
    ]);
    $product = Product::factory()->for($user)->create([
        'generated_description' => null,
    ]);

    $timestamp = (string) now()->timestamp;
    $path = "api/products/{$product->id}/description";
    $signature = $this->signatureService->signatureFor('GET', $path, $timestamp, $apiClient->client_secret);

    $this->getJson("/{$path}", [
        'X-Describr-Client' => $apiClient->client_id,
        'X-Describr-Timestamp' => $timestamp,
        'X-Describr-Signature' => $signature,
    ])->assertNotFound();
});

it('forbids an api client from accessing another users product', function () {
    $owner = User::factory()->create(['email' => 'owner@example.com']);
    $otherUser = User::factory()->create(['email' => 'other@example.com']);
    $apiClient = ApiClient::factory()->for($owner)->create([
        'client_secret' => 'owner-secret',
    ]);
    $product = Product::factory()->completed()->for($otherUser)->create([
        'generated_description' => 'Other user description.',
    ]);

    GeneratedDescription::factory()->for($product)->create([
        'description' => 'Other user description.',
    ]);

    $timestamp = (string) now()->timestamp;
    $path = "api/products/{$product->id}/description";
    $signature = $this->signatureService->signatureFor('GET', $path, $timestamp, $apiClient->client_secret);

    $this->getJson("/{$path}", [
        'X-Describr-Client' => $apiClient->client_id,
        'X-Describr-Timestamp' => $timestamp,
        'X-Describr-Signature' => $signature,
    ])->assertForbidden()
        ->assertJsonPath('message', 'This API client cannot access the requested product.');
});
