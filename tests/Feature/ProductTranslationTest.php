<?php

use App\Jobs\TranslateGeneratedDescription;
use App\Models\GeneratedDescription;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('queues a DeepL translation for the latest product description', function () {
    Queue::fake();

    $user = User::factory()->create();
    $product = Product::factory()->completed()->for($user)->create();
    $description = GeneratedDescription::factory()->for($product)->create([
        'description' => 'A complete generated description.',
    ]);

    $this->actingAs($user)
        ->post(route('products.translations.store', $product), [
            'target_language' => 'RO',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('description_translations', [
        'generated_description_id' => $description->id,
        'target_language' => 'RO',
        'provider' => 'deepl',
        'status' => 'pending',
    ]);

    Queue::assertPushed(TranslateGeneratedDescription::class);
});

it('forbids translating another users product', function () {
    Queue::fake();

    $owner = User::factory()->create();
    $intruder = User::factory()->create(['email' => 'intruder@example.com']);
    $product = Product::factory()->completed()->for($owner)->create();
    GeneratedDescription::factory()->for($product)->create();

    $this->actingAs($intruder)
        ->post(route('products.translations.store', $product), [
            'target_language' => 'RO',
        ])
        ->assertForbidden();

    Queue::assertNothingPushed();
});

it('does not queue a duplicate translation for the same language', function () {
    Queue::fake();

    $user = User::factory()->create(['email' => 'duplicate@example.com']);
    $product = Product::factory()->completed()->for($user)->create();
    $description = GeneratedDescription::factory()->for($product)->create();

    $description->translations()->create([
        'target_language' => 'RO',
        'provider' => 'deepl',
        'status' => 'completed',
        'translated_text' => 'Descriere tradusa.',
    ]);

    $this->actingAs($user)
        ->from(route('products.show', $product))
        ->post(route('products.translations.store', $product), [
            'target_language' => 'RO',
        ])
        ->assertRedirect(route('products.show', $product))
        ->assertSessionHasErrors('target_language');

    expect($description->translations()->where('target_language', 'RO')->count())->toBe(1);

    Queue::assertNothingPushed();
});
