<?php

namespace App\Services;

use App\Exceptions\SyntheticProvisioningFailure;
use App\Jobs\ProvisionEntitlement;
use App\Models\Account;
use App\Models\Activity;
use App\Models\AuditRun;
use App\Models\Entitlement;
use App\Models\Incident;
use App\Models\Invoice;
use App\Models\LabRun;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\ProvisioningAttempt;
use App\Models\ReconciliationAction;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEvent;
use App\Models\Workspace;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GuidedDemoService
{
    public function __construct(
        private readonly DemoScenarioSeeder $seeder,
        private readonly WebhookIngestor $webhooks,
        private readonly WebhookSignature $signatures,
        private readonly EntitlementProvisioner $provisioner,
        private readonly RevenueIntegrityAuditor $auditor,
        private readonly ReconciliationService $reconciliation,
    ) {}

    public function start(Workspace $workspace): LabRun
    {
        $this->seeder->seed($workspace);

        return DB::transaction(function () use ($workspace): LabRun {
            $fragile = LabRun::query()
                ->whereBelongsTo($workspace)
                ->where('mode', 'fragile')
                ->firstOrFail();
            $accountId = $fragile->account_id;
            $subscriptionId = $fragile->subscription_id;
            $paymentId = $fragile->payment_id;
            $invoiceId = Payment::query()->findOrFail($paymentId)->invoice_id;

            Incident::query()->where('workspace_id', $workspace->id)->delete();
            Activity::query()->where('workspace_id', $workspace->id)->delete();
            AuditRun::query()->where('workspace_id', $workspace->id)->delete();
            $fragile->delete();
            Payment::query()->whereKey($paymentId)->delete();
            Invoice::query()->whereKey($invoiceId)->delete();
            Subscription::query()->whereKey($subscriptionId)->delete();
            Account::query()->whereKey($accountId)->delete();

            $now = now()->toImmutable();
            $plan = Plan::query()->whereBelongsTo($workspace)->where('code', 'scale-annual')->firstOrFail();
            $account = Account::query()->create([
                'workspace_id' => $workspace->id,
                'external_id' => 'org_northstar_fragile',
                'name' => 'Northstar Labs',
                'segment' => 'Synthetic enterprise',
                'owner_name' => 'Maya Chen',
            ]);
            $subscription = Subscription::query()->create([
                'workspace_id' => $workspace->id,
                'account_id' => $account->id,
                'plan_id' => $plan->id,
                'external_id' => 'sub_northstar_fragile',
                'plan' => $plan->name,
                'status' => 'pending',
                'expected_monthly_amount' => $plan->monthly_price,
                'currency' => $plan->currency,
                'access_sla_seconds' => config('rescue_lab.entitlement_sla_seconds'),
                'started_at' => $now->subYear()->startOfMonth(),
            ]);
            $invoice = Invoice::query()->create([
                'workspace_id' => $workspace->id,
                'account_id' => $account->id,
                'subscription_id' => $subscription->id,
                'external_id' => 'inv_northstar_fragile',
                'period_start' => $now->startOfMonth(),
                'period_end' => $now->endOfMonth(),
                'amount' => $plan->monthly_price,
                'currency' => $plan->currency,
                'status' => 'paid',
                'due_date' => $now->addDays(7),
            ]);
            $payment = Payment::query()->create([
                'workspace_id' => $workspace->id,
                'invoice_id' => $invoice->id,
                'external_id' => 'pay_northstar_fragile',
                'amount' => $plan->monthly_price,
                'status' => 'succeeded',
                'attempted_at' => $now->subMinutes(10),
            ]);

            return LabRun::query()->create([
                'workspace_id' => $workspace->id,
                'account_id' => $account->id,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'scenario_key' => 'northstar-paid-access-v1',
                'mode' => 'fragile',
                'correlation_id' => (string) Str::uuid(),
                'status' => 'payment_succeeded',
                'sla_threshold_ms' => $subscription->access_sla_seconds * 1000,
                'evidence' => ['synthetic' => true, 'guided' => true],
                'started_at' => $now,
            ]);
        });
    }

    /** @return array{message: string, focus: string} */
    public function advance(Workspace $workspace, ?User $user = null): array
    {
        $run = $this->guidedRun($workspace);

        if (! $run || $this->completedSteps($run) >= 8) {
            $this->start($workspace);

            return ['message' => 'Successful synthetic payment recorded.', 'focus' => 'control'];
        }

        if ($run->deliveries()->count() === 0) {
            $this->deliverWebhooks($workspace, $run);

            return ['message' => 'Ten signed deliveries retained as one provider event and one business action.', 'focus' => 'incident'];
        }

        if ($run->attempts()->count() < 3) {
            $attempt = $this->failProvisioningAttempt($run);

            return [
                'message' => "Provisioning attempt {$attempt->attempt_number} failed with a controlled provider timeout.",
                'focus' => 'root',
            ];
        }

        $incident = Incident::query()->where('lab_run_id', $run->id)->first();

        if (! $incident) {
            $this->detectIncident($workspace, $run, $user);

            return ['message' => 'SLA audit detected one paid-but-locked account and USD 4,800 exposure.', 'focus' => 'control'];
        }

        $action = $this->reconciliation->reconcile($incident, $user);

        return [
            'message' => $action->result === 'repaired'
                ? 'Safe reconciliation restored access with one business effect.'
                : 'Repeat reconciliation verified no_op with zero business effects.',
            'focus' => 'recovery',
        ];
    }

    /** @return array<string, mixed> */
    public function state(Workspace $workspace, ?LabRun $run = null): array
    {
        $run ??= LabRun::query()->whereBelongsTo($workspace)->where('mode', 'fragile')->first();
        $active = $run && data_get($run->evidence, 'guided') === true;
        $steps = [
            ['key' => 'payment', 'title' => 'Payment succeeds', 'detail' => 'Create the synthetic paid subscription and correlation trail.'],
            ['key' => 'webhooks', 'title' => 'Deliver webhook 10x', 'detail' => 'Retain every delivery while consuming one provider event once.'],
            ['key' => 'attempt-1', 'title' => 'Queue attempt 1', 'detail' => 'Persist the first controlled provisioning timeout.'],
            ['key' => 'attempt-2', 'title' => 'Queue attempt 2', 'detail' => 'Retry the same entitlement without creating another one.'],
            ['key' => 'attempt-3', 'title' => 'Queue attempt 3', 'detail' => 'Exhaust the bounded retry policy and retain failure evidence.'],
            ['key' => 'detect', 'title' => 'Detect SLA breach', 'detail' => 'Translate locked access into one incident and USD 4,800 exposure.'],
            ['key' => 'repair', 'title' => 'Repair once', 'detail' => 'Activate the existing entitlement under transaction and row locks.'],
            ['key' => 'verify', 'title' => 'Verify repeat no-op', 'detail' => 'Run recovery again and prove zero additional business effects.'],
        ];
        $completed = $active ? $this->completedSteps($run) : 0;
        $attempts = $active ? $run->attempts()->orderBy('attempt_number')->get() : collect();
        $incident = $active ? Incident::query()->where('lab_run_id', $run->id)->first() : null;
        $actions = $active ? ReconciliationAction::query()->where('lab_run_id', $run->id)->orderBy('created_at')->get() : collect();
        $observed = [
            $active ? 'succeeded / USD '.number_format((float) $run->payment->amount) : 'waiting',
            $active ? $run->deliveries()->count().' deliveries / '.$run->events()->count().' event / '.$run->business_actions.' effect' : 'waiting',
            $attempts->firstWhere('attempt_number', 1)?->status ?? 'waiting',
            $attempts->firstWhere('attempt_number', 2)?->status ?? 'waiting',
            $attempts->firstWhere('attempt_number', 3)?->status ?? 'waiting',
            $incident ? $incident->status.' / USD '.number_format((float) $incident->revenue_impact) : 'waiting',
            $actions->firstWhere('result', 'repaired') ? 'active / 1 effect' : 'waiting',
            $actions->firstWhere('result', 'no_op') ? 'no_op / 0 effects' : 'waiting',
        ];

        $steps = collect($steps)->map(function (array $step, int $index) use ($active, $completed, $observed): array {
            $step['status'] = ! $active || $index > $completed ? 'pending' : ($index < $completed ? 'complete' : 'current');
            $step['observed'] = $observed[$index];

            return $step;
        })->all();
        $labels = [
            'Start: record successful payment',
            'Deliver the same webhook 10 times',
            'Run queue attempt 1',
            'Run queue attempt 2',
            'Run queue attempt 3',
            'Run paid-access SLA audit',
            'Repair access safely',
            'Verify repeat no-op',
            'Replay guided incident',
        ];

        return [
            'enabled' => (bool) config('rescue_lab.demo_mutations_enabled'),
            'active' => (bool) $active,
            'completed' => $completed,
            'total' => count($steps),
            'progress' => (int) round(($completed / count($steps)) * 100),
            'complete' => $completed === count($steps),
            'nextLabel' => $active ? $labels[$completed] : $labels[0],
            'correlationId' => $active ? $run->correlation_id : null,
            'slaThresholdMs' => $active ? $run->sla_threshold_ms : (int) config('rescue_lab.entitlement_sla_seconds') * 1000,
            'facts' => [
                'deliveries' => $active ? $run->deliveries()->count() : 0,
                'events' => $active ? $run->events()->count() : 0,
                'businessActions' => $active ? $run->business_actions : 0,
                'queueAttempts' => $active ? $run->attempts()->count() : 0,
                'queueFailures' => $active ? $run->attempts()->where('status', 'failed')->count() : 0,
                'access' => $active ? ($run->entitlements()->first()?->state ?? 'not requested') : 'not started',
                'incident' => $incident?->status ?? 'not detected',
                'revenue' => $incident ? (float) $incident->revenue_impact : 0,
                'lastResult' => $actions->last()?->result ?? 'none',
            ],
            'steps' => $steps,
        ];
    }

    private function guidedRun(Workspace $workspace): ?LabRun
    {
        return LabRun::query()
            ->whereBelongsTo($workspace)
            ->where('mode', 'fragile')
            ->get()
            ->first(fn (LabRun $run): bool => data_get($run->evidence, 'guided') === true);
    }

    private function completedSteps(LabRun $run): int
    {
        $completed = 1;
        $completed += $run->deliveries()->count() > 0 ? 1 : 0;
        $completed += min(3, $run->attempts()->count());
        $completed += Incident::query()->where('lab_run_id', $run->id)->exists() ? 1 : 0;
        $completed += ReconciliationAction::query()->where('lab_run_id', $run->id)->where('result', 'repaired')->exists() ? 1 : 0;
        $completed += ReconciliationAction::query()->where('lab_run_id', $run->id)->where('result', 'no_op')->exists() ? 1 : 0;

        return $completed;
    }

    private function deliverWebhooks(Workspace $workspace, LabRun $run): void
    {
        $payload = [
            'event_id' => 'evt_payment_succeeded_fragile',
            'type' => 'payment.succeeded',
            'account_id' => $run->account->external_id,
            'subscription_id' => $run->subscription->external_id,
            'payment_id' => $run->payment->external_id,
            'amount' => (float) $run->payment->amount,
            'currency' => 'USD',
            'synthetic' => true,
        ];
        $signature = $this->signatures->sign($payload);

        foreach (range(1, 10) as $deliveryNumber) {
            $this->webhooks->ingest(
                $workspace,
                $payload,
                $signature,
                'dlv_guided_'.str_pad((string) $deliveryNumber, 2, '0', STR_PAD_LEFT),
                $run,
                false,
            );
        }
    }

    private function failProvisioningAttempt(LabRun $run): ProvisioningAttempt
    {
        $entitlement = Entitlement::query()->where('lab_run_id', $run->id)->firstOrFail();
        $event = WebhookEvent::query()->where('lab_run_id', $run->id)->firstOrFail();
        $job = new ProvisionEntitlement($entitlement->id, $run->id, $event->id);

        try {
            $job->handle($this->provisioner);
        } catch (SyntheticProvisioningFailure) {
            // The failed attempt is the controlled evidence requested by this guided action.
        }

        return ProvisioningAttempt::query()->where('lab_run_id', $run->id)->latest('attempt_number')->firstOrFail();
    }

    private function detectIncident(Workspace $workspace, LabRun $run, ?User $user): void
    {
        $entitlement = Entitlement::query()->where('lab_run_id', $run->id)->firstOrFail();
        $event = WebhookEvent::query()->where('lab_run_id', $run->id)->firstOrFail();
        $delayMs = 600_000;
        $evidence = array_merge($run->evidence ?? [], [
            'provider_event_id' => $event->provider_event_id,
            'provider_delivery_ids' => WebhookDelivery::query()->where('lab_run_id', $run->id)->pluck('provider_delivery_id')->all(),
            'entitlement_id' => $entitlement->id,
            'attempt_ids' => ProvisioningAttempt::query()->where('lab_run_id', $run->id)->pluck('id')->all(),
        ]);

        $run->update([
            'status' => 'failed',
            'entitlement_state' => 'locked',
            'payment_to_access_ms' => $delayMs,
            'sla_breached' => true,
            'before_state' => ['payment' => 'succeeded', 'subscription' => 'active', 'entitlement' => 'locked'],
            'processing_ms' => ProvisioningAttempt::query()->where('lab_run_id', $run->id)->sum('duration_ms'),
            'evidence' => $evidence,
            'completed_at' => now(),
        ]);

        $this->auditor->run($workspace, $user);
    }
}
