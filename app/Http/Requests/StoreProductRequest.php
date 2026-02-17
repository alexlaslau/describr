<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'links' => 'required|array|min:1',
            'links.*' => 'required|url|max:1024',
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
