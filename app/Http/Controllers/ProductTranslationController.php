<?php

namespace App\Http\Controllers;

use App\Jobs\TranslateGeneratedDescription;
use App\Models\DescriptionTranslation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductTranslationController extends Controller
{
    public function store(Request $request, Product $product)
    {
        abort_if($product->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'target_language' => 'required|string|in:' . implode(',', array_keys(config('app.describr.description_translation_languages'))),
        ]);

        $latestDescription = $product->generatedDescriptions()->first();

        $targetLanguage = strtoupper($validated['target_language']);

        $existingTranslation = DescriptionTranslation::query()
            ->where('generated_description_id', $latestDescription->id)
            ->where('target_language', $targetLanguage)
            ->first();

        if ($existingTranslation) {
            return back()->withErrors([
                'target_language' => "A translation for {$targetLanguage} already exists.",
            ]);
        }

        $translation = DescriptionTranslation::create([
            'generated_description_id' => $latestDescription->id,
            'target_language' => $targetLanguage,
            'status' => 'pending',
        ]);

        TranslateGeneratedDescription::dispatch($translation);

        return back();
    }
}
