<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'links' => 'required|array|min:1|max:' . config('app.describr.max_links_per_product'),
            'links.*' => 'required|url|max:1024',
            'ai_provider' => 'required|string|in:openai,anthropic',
            'target_audience' => 'required|string|max:255',
            'tone' => 'required|string|max:255',
            'custom_details' => 'nullable|string|max:300',
        ];
    }

    public function cleanedLinks(): array
    {
        return collect($this->validated('links'))
            ->map(fn ($url) => strtok(trim($url), '?'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
