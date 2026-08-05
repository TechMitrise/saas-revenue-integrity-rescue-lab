<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\AuditRun;
use App\Models\Incident;
use App\Models\LabRun;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class RevenueIntegrityAuditor
{
    /** @return array{audit: AuditRun, detected: int, created: int, refreshed: int} */
    public function run(Workspace $workspace, ?User $initiator = null, ?CarbonImmutable $at = null): array
    {
        $startedAt = $at ?? now()->toImmutable();
        $timer = hrtime(true);

        return DB::transaction(function () use ($workspace, $initiator, $startedAt, $timer): array {
            $runs = LabRun::query()
                ->whereBelongsTo($workspace)
                ->with(['account', 'subscription', 'payment', 'deliveries', 'events', 'attempts', 'entitlements'])
                ->get();
            $findings = $runs->filter(function (LabRun $run): bool {
                $entitlement = $run->entitlements->first();

                return $run->payment->status === 'succeeded'
                    && $run->subscription->status === 'active'
                    && $entitlement?->state === 'locked'
                    && $run->sla_breached;
            });
            $created = 0;

            foreach ($findings as $run) {
                $entitlement = $run->entitlements->firstOrFail();
                $event = $run->events->first();
                $incident = Incident::query()->firstOrNew([
                    'workspace_id' => $workspace->id,
                    'fingerprint' => "paid_but_locked:{$run->subscription_id}",
                ]);

                if (! $incident->exists) {
                    $incident->status = 'open';
                    $incident->detected_at = $startedAt;
                    $created++;
                }

                $incident->fill([
                    'lab_run_id' => $run->id,
                    'account_id' => $run->account_id,
                    'subscription_id' => $run->subscription_id,
                    'invoice_id' => $run->payment->invoice_id,
                    'type' => 'paid_but_locked',
                    'severity' => 'critical',
                    'title' => 'Payment succeeded. Access failed.',
                    'reason' => "{$run->account->name} paid successfully but remained locked beyond the entitlement SLA.",
                    'root_cause' => 'At-least-once webhook delivery reached a fragile provisioning path; the queued entitlement job exhausted bounded retries after deterministic provider timeouts.',
                    'recommended_action' => 'Run lock-protected reconciliation, verify the existing entitlement becomes active, then repeat to prove a no-op.',
                    'revenue_impact' => $run->subscription->expected_monthly_amount,
                    'currency' => $run->subscription->currency,
                    'evidence' => [
                        'synthetic' => true,
                        'correlation_id' => $run->correlation_id,
                        'payment_id' => $run->payment->external_id,
                        'provider_event_id' => $event?->provider_event_id,
                        'provider_delivery_ids' => $run->deliveries->pluck('provider_delivery_id')->all(),
                        'duplicate_deliveries' => max(0, $run->deliveries->count() - 1),
                        'job_uuids' => $run->attempts->pluck('job_uuid')->all(),
                        'queue_failures' => $run->queue_failures,
                        'entitlement_id' => $entitlement->id,
                        'entitlement_state' => $entitlement->state,
                        'payment_to_access_ms' => $run->payment_to_access_ms,
                        'sla_threshold_ms' => $run->sla_threshold_ms,
                        'query_count' => $run->query_count,
                        'processing_ms' => $run->processing_ms,
                    ],
                    'last_detected_at' => $startedAt,
                    'sla_breached_at' => $startedAt,
                ]);
                $incident->save();
            }

            $recordsScanned = $runs->sum(fn (LabRun $run): int => 3 + $run->deliveries->count() + $run->attempts->count() + $run->entitlements->count());
            $audit = AuditRun::query()->create([
                'workspace_id' => $workspace->id,
                'initiated_by' => $initiator?->id,
                'status' => 'completed',
                'records_scanned' => $recordsScanned,
                'findings_count' => $findings->count(),
                'new_incidents_count' => $created,
                'duration_ms' => max(1, (int) ((hrtime(true) - $timer) / 1_000_000)),
                'started_at' => $startedAt,
                'completed_at' => $startedAt,
            ]);

            Activity::query()->create([
                'workspace_id' => $workspace->id,
                'user_id' => $initiator?->id,
                'type' => 'paid_access_audit_completed',
                'message' => "Paid-access audit completed: {$findings->count()} paid-but-locked incident(s).",
                'context' => ['audit_id' => $audit->id],
            ]);

            return [
                'audit' => $audit,
                'detected' => $findings->count(),
                'created' => $created,
                'refreshed' => $findings->count() - $created,
            ];
        });
    }
}
