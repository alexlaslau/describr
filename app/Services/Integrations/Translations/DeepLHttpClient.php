<?php

namespace App\Services\Integrations\Translations;

use App\Enums\DeepLEndpoint;
use App\Enums\HttpMethod;
use App\Exceptions\DeepLApiException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class DeepLHttpClient
{
    public function request(
        DeepLEndpoint $endpoint,
        HttpMethod $method,
        array $data = [],
    ): array
    {
        try {
            $options = match ($method) {
                HttpMethod::GET => ['query' => $data],
                default => ['json' => $data],
            };

            $response = Http::timeout(20)
                ->retry(3, 250, fn ($exception) => $exception->response?->status() >= 500)
                ->withHeaders([
                    'Authorization' => 'DeepL-Auth-Key ' . config('services.deepl.key'),
                ])
                ->send($method->value, $endpoint->url(), $options)
                ->throw();
        } catch (\Throwable $exception) {
            throw new DeepLApiException(
                data_get($exception->response?->json(), 'message'),
                previous: $exception
            );
        }

        return $response->json();
    }
}
