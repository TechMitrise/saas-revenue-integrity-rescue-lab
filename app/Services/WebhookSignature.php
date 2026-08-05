<?php

namespace App\Services;

class WebhookSignature
{
    /** @param array<string, mixed> $payload */
    public function sign(array $payload): string
    {
        return hash_hmac('sha256', $this->canonical($payload), (string) config('rescue_lab.webhook_secret'));
    }

    /** @param array<string, mixed> $payload */
    public function verify(array $payload, string $signature): bool
    {
        return hash_equals($this->sign($payload), $signature);
    }

    /** @param array<string, mixed> $payload */
    public function hash(array $payload): string
    {
        return hash('sha256', $this->canonical($payload));
    }

    /** @param array<string, mixed> $payload */
    private function canonical(array $payload): string
    {
        ksort($payload);

        return (string) json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
