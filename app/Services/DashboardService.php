<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\LabRun;
use App\Models\ReconciliationAction;
use App\Models\Workspace;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(private readonly GuidedDemoService $guided) {}

    /** @return array<string, mixed> */
    public function for(Workspace $workspace): array
    {
        $runs = LabRun::query()
            ->whereBelongsTo($workspace)
            ->with(['account', 'subscription', 'payment', 'deliveries', 'events', 'attempts', 'entitlements', 'reconciliations'])
            ->orderByRaw("FIELD(mode, 'fragile', 'stabilised')")
            ->get();
        $fragile = $runs->firstWhere('mode', 'fragile');
        $incident = Incident::query()
            ->whereBelongsTo($workspace)
            ->where('type', 'paid_but_locked')
            ->with('account')
            ->first();

        return [
            'workspace' => ['name' => $workspace->name, 'currency' => $workspace->currency],
            'guarantee' => 'At-least-once event delivery with an idempotent, effectively-once business effect.',
            'syntheticDisclosure' => 'All organisations, payments, events, failures, timings, and revenue figures are synthetic lab evidence.',
            'guide' => $this->guided->state($workspace, $fragile),
            'metrics' => [
                'customer' => $fragile?->account->name,
                'revenueAtRisk' => $incident && in_array($incident->status, ['open', 'investigating'], true) ? (float) $incident->revenue_impact : 0,
                'monthlyRevenue' => $fragile ? (float) $fragile->subscription->expected_monthly_amount : 0,
                'paymentToAccessMs' => $fragile?->payment_to_access_ms,
                'duplicateDeliveries' => $fragile ? max(0, $fragile->webhook_deliveries - 1) : 0,
                'queueFailures' => $fragile?->queue_failures ?? 0,
                'status' => $incident?->status ?? 'not_detected',
                'accessState' => $fragile?->entitlements->first()?->state,
                'slaThresholdMs' => $fragile?->sla_threshold_ms ?? (int) config('rescue_lab.entitlement_sla_seconds') * 1000,
            ],
            'incident' => $incident ? $this->incident($incident, $fragile) : null,
            'timeline' => $fragile ? $this->timeline($fragile) : [],
            'comparison' => $runs->map($this->run(...))->values(),
            'reconciliations' => $fragile?->reconciliations->sortBy('created_at')->map(fn (ReconciliationAction $action): array => [
                'id' => $action->action_id,
                'result' => $action->result,
                'businessEffects' => $action->business_effects,
                'before' => $action->before_state,
                'after' => $action->after_state,
                'reason' => $action->reason,
                'at' => $action->created_at->toIso8601String(),
            ])->values() ?? [],
            'engineering' => [
                ['label' => 'Domain and feature tests', 'path' => 'tests/Feature/PaidAccessRescueTest.php', 'command' => './vendor/bin/sail test'],
                ['label' => 'Desktop and mobile journey', 'path' => 'tests/Browser/revenue-rescue.spec.js', 'command' => './vendor/bin/sail npm run test:e2e'],
                ['label' => 'Webhook and reconciliation architecture', 'path' => 'docs/ARCHITECTURE.md', 'command' => 'Open architecture evidence'],
                ['label' => 'Operations and recovery runbook', 'path' => 'docs/OPERATIONS.md', 'command' => 'Open runbook evidence'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function incident(Incident $incident, ?LabRun $run): array
    {
        return [
            'id' => $incident->id,
            'accountName' => $incident->account->name,
            'title' => $incident->title,
            'status' => $incident->status,
            'severity' => $incident->severity,
            'reason' => $incident->reason,
            'rootCause' => $incident->root_cause,
            'recommendedAction' => $incident->recommended_action,
            'revenueImpact' => (float) $incident->revenue_impact,
            'revenueFormula' => 'Synthetic monthly revenue exposed = the active paid subscription monthly amount while access remains locked.',
            'currency' => $incident->currency,
            'evidence' => $incident->evidence,
            'slaBreachedAt' => $incident->sla_breached_at?->toIso8601String(),
            'entitlementId' => $run?->entitlements->first()?->id,
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    private function timeline(LabRun $run): Collection
    {
        $event = $run->events->first();
        $items = collect([
            [
                'kind' => 'payment',
                'title' => 'Synthetic payment succeeded',
                'detail' => $run->payment->external_id.' / USD '.number_format((float) $run->payment->amount),
                'state' => 'success',
                'at' => $run->payment->attempted_at->toIso8601String(),
            ],
        ]);

        if ($event) {
            $items->push([
                'kind' => 'webhook',
                'title' => 'Signed provider event accepted',
                'detail' => $event->provider_event_id.' / 1 event, '.$run->webhook_deliveries.' deliveries',
                'state' => 'warning',
                'at' => $event->received_at->toIso8601String(),
            ]);
        }

        foreach ($run->attempts->sortBy('attempt_number') as $attempt) {
            $items->push([
                'kind' => 'queue',
                'title' => "Provisioning attempt {$attempt->attempt_number}: {$attempt->status}",
                'detail' => $attempt->job_uuid.' / '.($attempt->failure_code ?? 'access activated').' / '.$attempt->duration_ms.'ms',
                'state' => $attempt->status === 'succeeded' ? 'success' : 'danger',
                'at' => $attempt->completed_at->toIso8601String(),
            ]);
        }

        if ($run->sla_breached) {
            $items->push([
                'kind' => 'sla',
                'title' => 'Entitlement SLA breached',
                'detail' => $run->payment_to_access_ms.'ms elapsed / '.$run->sla_threshold_ms.'ms SLA',
                'state' => 'danger',
                'at' => $run->completed_at?->toIso8601String(),
            ]);
        }

        foreach ($run->reconciliations->sortBy('created_at') as $action) {
            $items->push([
                'kind' => 'reconciliation',
                'title' => "Reconciliation {$action->result}",
                'detail' => $action->action_id.' / '.$action->business_effects.' business effect(s)',
                'state' => 'success',
                'at' => $action->created_at->toIso8601String(),
            ]);
        }

        return $items->sortBy('at')->values();
    }

    /** @return array<string, mixed> */
    private function run(LabRun $run): array
    {
        return [
            'mode' => $run->mode,
            'scenarioKey' => $run->scenario_key,
            'correlationId' => $run->correlation_id,
            'webhookDeliveries' => $run->webhook_deliveries,
            'providerEvents' => $run->events->count(),
            'businessActions' => $run->business_actions,
            'queueAttempts' => $run->queue_attempts,
            'queueFailures' => $run->queue_failures,
            'entitlementState' => $run->entitlements->first()?->state,
            'paymentToAccessMs' => $run->payment_to_access_ms,
            'slaThresholdMs' => $run->sla_threshold_ms,
            'slaBreached' => $run->sla_breached,
            'reconciliationResult' => $run->reconciliation_result,
            'before' => $run->before_state,
            'after' => $run->after_state,
            'queryCount' => $run->query_count,
            'processingMs' => $run->processing_ms,
            'controls' => $run->mode === 'stabilised' ? [
                'Signature verification', 'Persistent inbox', 'Unique provider event', 'Idempotent consumer',
                'Unique entitlement', 'Bounded retries', 'Transactional reconciliation',
            ] : ['No recurrence controls applied in fragile mode'],
        ];
    }
}
