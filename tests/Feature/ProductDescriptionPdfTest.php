<?php

use App\Models\GeneratedDescription;
use App\Models\Product;
use App\Models\User;

it('downloads a pdf for the original generated description', function () {
    $user = User::factory()->create();
    $product = Product::factory()->completed()->for($user)->create([
        'name' => 'Demo Product',
        'generated_description' => 'Original generated description.',
    ]);

    GeneratedDescription::factory()->for($product)->create([
        'description' => 'Original generated description.',
    ]);

    $response = $this->actingAs($user)
        ->get(route('products.descriptions.pdf', $product));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('downloads a pdf for a completed translated description', function () {
    $user = User::factory()->create();
    $product = Product::factory()->completed()->for($user)->create([
        'name' => 'Translated Demo Product',
        'generated_description' => 'Original generated description.',
    ]);

    $description = GeneratedDescription::factory()->for($product)->create([
        'description' => 'Original generated description.',
    ]);

    $translation = $description->translations()->create([
        'target_language' => 'RO',
        'provider' => 'deepl',
        'status' => 'completed',
        'translated_text' => 'Descriere tradusa.',
        'translated_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('products.translations.pdf', [$product, $translation]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});
