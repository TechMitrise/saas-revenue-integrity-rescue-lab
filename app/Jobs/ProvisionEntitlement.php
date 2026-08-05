<?php

namespace App\Jobs;

use App\Models\Entitlement;
use App\Models\LabRun;
use App\Models\WebhookEvent;
use App\Services\EntitlementProvisioner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProvisionEntitlement implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public readonly int $entitlementId,
        public readonly int $labRunId,
        public readonly ?int $webhookEventId = null,
    ) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return config('rescue_lab.provisioning_backoff_seconds');
    }

    public function handle(EntitlementProvisioner $provisioner): void
    {
        $provisioner->provision(
            Entitlement::query()->findOrFail($this->entitlementId),
            LabRun::query()->findOrFail($this->labRunId),
            $this->webhookEventId ? WebhookEvent::query()->find($this->webhookEventId) : null,
        );
    }
}
