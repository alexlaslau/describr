<?php

namespace App\Enums;

enum DeepLEndpoint: string
{
    case TRANSLATE_TEXT = '/v2/translate';
    case LANGUAGES = '/v2/languages';
    case USAGE = '/v2/usage';

    public function url(): string
    {
        $baseUrl = config('services.deepl.base_url');

        return $baseUrl . $this->value;
    }
}
