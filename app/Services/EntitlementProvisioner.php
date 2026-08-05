<?php

namespace App\Services;

use App\Exceptions\SyntheticProvisioningFailure;
use App\Models\Entitlement;
use App\Models\LabRun;
use App\Models\ProvisioningAttempt;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EntitlementProvisioner
{
    public function provision(Entitlement $entitlement, LabRun $run, ?WebhookEvent $event = null): ProvisioningAttempt
    {
        $failure = false;

        $attempt = DB::transaction(function () use ($entitlement, $run, $event, &$failure): ProvisioningAttempt {
            $entitlement = Entitlement::query()->whereKey($entitlement->id)->lockForUpdate()->firstOrFail();
            $run = LabRun::query()->whereKey($run->id)->lockForUpdate()->firstOrFail();
            $attemptNumber = ProvisioningAttempt::query()->where('entitlement_id', $entitlement->id)->count() + 1;
            $startedAt = now();

            if ($entitlement->state === 'active') {
                return ProvisioningAttempt::query()->create([
                    'workspace_id' => $entitlement->workspace_id,
                    'lab_run_id' => $run->id,
                    'entitlement_id' => $entitlement->id,
                    'webhook_event_id' => $event?->id,
                    'job_uuid' => (string) Str::uuid(),
                    'attempt_number' => $attemptNumber,
                    'status' => 'no_op',
                    'duration_ms' => 1,
                    'started_at' => $startedAt,
                    'completed_at' => $startedAt,
                ]);
            }

            if ($run->mode === 'fragile') {
                $durations = [730, 1180, 1640];
                $failure = true;
                $attempt = ProvisioningAttempt::query()->create([
                    'workspace_id' => $entitlement->workspace_id,
                    'lab_run_id' => $run->id,
                    'entitlement_id' => $entitlement->id,
                    'webhook_event_id' => $event?->id,
                    'job_uuid' => (string) Str::uuid(),
                    'attempt_number' => $attemptNumber,
                    'status' => 'failed',
                    'failure_code' => 'synthetic_provider_timeout',
                    'failure_message' => 'Synthetic lab provider timed out before access activation.',
                    'duration_ms' => $durations[$attemptNumber - 1] ?? 1640,
                    'started_at' => $startedAt,
                    'completed_at' => $startedAt,
                ]);

                $run->update([
                    'queue_attempts' => $attemptNumber,
                    'queue_failures' => $attemptNumber,
                    'entitlement_state' => 'locked',
                    'status' => $attemptNumber >= (int) config('rescue_lab.provisioning_max_attempts') ? 'failed' : 'retrying',
                ]);

                return $attempt;
            }

            $entitlement->update(['state' => 'active', 'provisioned_at' => $startedAt]);
            $attempt = ProvisioningAttempt::query()->create([
                'workspace_id' => $entitlement->workspace_id,
                'lab_run_id' => $run->id,
                'entitlement_id' => $entitlement->id,
                'webhook_event_id' => $event?->id,
                'job_uuid' => (string) Str::uuid(),
                'attempt_number' => $attemptNumber,
                'status' => 'succeeded',
                'duration_ms' => 420,
                'started_at' => $startedAt,
                'completed_at' => $startedAt,
            ]);
            $run->update([
                'queue_attempts' => $attemptNumber,
                'queue_failures' => 0,
                'entitlement_state' => 'active',
                'payment_to_access_ms' => 420,
                'sla_breached' => false,
                'status' => 'completed',
                'completed_at' => $startedAt,
            ]);

            return $attempt;
        });

        if ($failure) {
            throw new SyntheticProvisioningFailure('Synthetic entitlement provider timeout.');
        }

        return $attempt;
    }
}
