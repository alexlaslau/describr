<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use App\Services\Security\HmacSignatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHmacSignature
{
    public function __construct(
        private readonly HmacSignatureService $signatureService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $clientId = $request->header('X-Describr-Client', '');
        $timestamp = $request->header('X-Describr-Timestamp', '');
        $signature = $request->header('X-Describr-Signature', '');

        if ($clientId === '' || $timestamp === '' || $signature === '') {
            return response()->json([
                'message' => 'Missing HMAC authentication headers.',
            ], 401);
        }

        $apiClient = ApiClient::query()
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->first();

        if (! $apiClient) {
            return response()->json([
                'message' => 'Unknown API client.',
            ], 401);
        }

        if (! $this->signatureService->isFresh($timestamp)) {
            return response()->json([
                'message' => 'Expired HMAC signature.',
            ], 401);
        }

        if (! $this->signatureService->isValid(
            $request->method(),
            $request->path(),
            $timestamp,
            $signature,
            $apiClient->client_secret,
        )) {
            return response()->json([
                'message' => 'Invalid HMAC signature.',
            ], 401);
        }

        $request->attributes->set('api_client', $apiClient);

        return $next($request);
    }
}
