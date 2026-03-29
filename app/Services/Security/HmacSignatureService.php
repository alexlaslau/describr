<?php

namespace App\Services\Security;

class HmacSignatureService
{
    public function signatureFor(string $method, string $path, string $timestamp, string $secret): string
    {
        return hash_hmac(
            'sha256',
            $this->payload($method, $path, $timestamp),
            $secret,
        );
    }

    public function isValid(string $method, string $path, string $timestamp, string $signature, string $secret): bool
    {
        return hash_equals(
            $this->signatureFor($method, $path, $timestamp, $secret),
            $signature,
        );
    }

    public function isFresh(string $timestamp): bool
    {
        if (!ctype_digit($timestamp)) {
            return false;
        }

        return abs(now()->timestamp - (int) $timestamp) <= 300;
    }

    private function payload(string $method, string $path, string $timestamp): string
    {
        return strtoupper($method) . "\n" . ltrim($path, '/') . "\n" . $timestamp;
    }
}
