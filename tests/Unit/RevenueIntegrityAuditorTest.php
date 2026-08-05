<?php

namespace Tests\Unit;

use App\Jobs\ProvisionEntitlement;
use App\Services\WebhookSignature;
use Tests\TestCase;

class RevenueIntegrityAuditorTest extends TestCase
{
    public function test_synthetic_webhook_signatures_are_deterministic_and_tamper_evident(): void
    {
        $signatures = resolve(WebhookSignature::class);
        $payload = ['event_id' => 'evt_test', 'amount' => 4800, 'synthetic' => true];
        $signature = $signatures->sign($payload);

        $this->assertTrue($signatures->verify($payload, $signature));
        $this->assertFalse($signatures->verify([...$payload, 'amount' => 1], $signature));
        $this->assertSame(64, strlen($signatures->hash($payload)));
    }

    public function test_provisioning_job_has_bounded_retries_backoff_and_timeout(): void
    {
        $job = new ProvisionEntitlement(1, 1, 1);

        $this->assertSame(3, $job->tries);
        $this->assertSame([1, 5, 15], $job->backoff());
        $this->assertSame(30, $job->timeout);
    }
}
