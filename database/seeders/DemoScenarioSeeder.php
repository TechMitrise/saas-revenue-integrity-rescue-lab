<?php

namespace Database\Seeders;

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
use App\Models\WebhookDelivery;
use App\Models\WebhookEvent;
use App\Models\Workspace;
use App\Services\EntitlementProvisioner;
use App\Services\RevenueIntegrityAuditor;
use App\Services\WebhookIngestor;
use App\Services\WebhookSignature;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoScenarioSeeder
{
    public function seed(Workspace $workspace, ?CarbonImmutable $at = null): void
    {
        $now = $at ?? now()->toImmutable();
        $this->clear($workspace);

        $plan = Plan::query()->create([
            'workspace_id' => $workspace->id,
            'code' => 'scale-annual',
            'name' => 'Scale Annual',
            'monthly_price' => 4800,
            'currency' => 'USD',
        ]);

        $this->simulate($workspace, $plan, 'fragile', $now);
        $this->simulate($workspace, $plan, 'stabilised', $now);
        resolve(RevenueIntegrityAuditor::class)->run($workspace, null, $now);
    }

    private function clear(Workspace $workspace): void
    {
        DB::transaction(function () use ($workspace): void {
            ReconciliationAction::query()->where('workspace_id', $workspace->id)->delete();
            ProvisioningAttempt::query()->where('workspace_id', $workspace->id)->delete();
            WebhookDelivery::query()->where('workspace_id', $workspace->id)->delete();
            WebhookEvent::query()->where('workspace_id', $workspace->id)->delete();
            Incident::query()->where('workspace_id', $workspace->id)->delete();
            Entitlement::query()->where('workspace_id', $workspace->id)->delete();
            LabRun::query()->where('workspace_id', $workspace->id)->delete();
            Payment::query()->where('workspace_id', $workspace->id)->delete();
            Invoice::query()->where('workspace_id', $workspace->id)->delete();
            Subscription::query()->where('workspace_id', $workspace->id)->delete();
            Account::query()->where('workspace_id', $workspace->id)->delete();
            Plan::query()->where('workspace_id', $workspace->id)->delete();
            Activity::query()->where('workspace_id', $workspace->id)->delete();
            AuditRun::query()->where('workspace_id', $workspace->id)->delete();
        });
    }

    private function simulate(Workspace $workspace, Plan $plan, string $mode, CarbonImmutable $now): LabRun
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $timer = hrtime(true);
        $suffix = $mode === 'fragile' ? 'fragile' : 'stabilised';
        $account = Account::query()->create([
            'workspace_id' => $workspace->id,
            'external_id' => "org_northstar_{$suffix}",
            'name' => $mode === 'fragile' ? 'Northstar Labs' : 'Northstar Labs / Stabilised replay',
            'segment' => 'Synthetic enterprise',
            'owner_name' => 'Maya Chen',
        ]);
        $subscription = Subscription::query()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'plan_id' => $plan->id,
            'external_id' => "sub_northstar_{$suffix}",
            'plan' => $plan->name,
            'status' => 'pending',
            'expected_monthly_amount' => $plan->monthly_price,
            'currency' => 'USD',
            'access_sla_seconds' => config('rescue_lab.entitlement_sla_seconds'),
            'started_at' => $now->subYear()->startOfMonth(),
        ]);
        $invoice = Invoice::query()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'subscription_id' => $subscription->id,
            'external_id' => "inv_northstar_{$suffix}",
            'period_start' => $now->startOfMonth(),
            'period_end' => $now->endOfMonth(),
            'amount' => $plan->monthly_price,
            'currency' => 'USD',
            'status' => 'paid',
            'due_date' => $now->addDays(7),
        ]);
        $payment = Payment::query()->create([
            'workspace_id' => $workspace->id,
            'invoice_id' => $invoice->id,
            'external_id' => "pay_northstar_{$suffix}",
            'amount' => $plan->monthly_price,
            'status' => 'succeeded',
            'attempted_at' => $mode === 'fragile' ? $now->subMinutes(10) : $now,
        ]);
        $run = LabRun::query()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'subscription_id' => $subscription->id,
            'payment_id' => $payment->id,
            'scenario_key' => 'northstar-paid-access-v1',
            'mode' => $mode,
            'correlation_id' => (string) Str::uuid(),
            'status' => 'running',
            'sla_threshold_ms' => $subscription->access_sla_seconds * 1000,
            'started_at' => $now,
        ]);
        $payload = [
            'event_id' => "evt_payment_succeeded_{$suffix}",
            'type' => 'payment.succeeded',
            'account_id' => $account->external_id,
            'subscription_id' => $subscription->external_id,
            'payment_id' => $payment->external_id,
            'amount' => (float) $payment->amount,
            'currency' => 'USD',
            'synthetic' => true,
        ];
        $signature = resolve(WebhookSignature::class)->sign($payload);
        $event = null;

        foreach (range(1, 10) as $deliveryNumber) {
            $result = resolve(WebhookIngestor::class)->ingest(
                $workspace,
                $payload,
                $signature,
                "dlv_{$suffix}_".str_pad((string) $deliveryNumber, 2, '0', STR_PAD_LEFT),
                $run,
                false,
            );
            $event = $result['event'];
        }

        $entitlement = Entitlement::query()->where('lab_run_id', $run->id)->firstOrFail();
        $job = new ProvisionEntitlement($entitlement->id, $run->id, $event?->id);
        $attempts = $mode === 'fragile' ? (int) config('rescue_lab.provisioning_max_attempts') : 1;

        foreach (range(1, $attempts) as $attempt) {
            try {
                $job->handle(resolve(EntitlementProvisioner::class));
            } catch (SyntheticProvisioningFailure) {
                // The deterministic failure is the evidence this controlled run is designed to retain.
            }
        }

        $elapsed = max(1, (int) ((hrtime(true) - $timer) / 1_000_000));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        $delayMs = $mode === 'fragile' ? $payment->attempted_at->diffInMilliseconds($now) : 420;
        $run->update([
            'entitlement_state' => $entitlement->fresh()->state,
            'payment_to_access_ms' => $delayMs,
            'sla_breached' => $delayMs > $run->sla_threshold_ms,
            'before_state' => [
                'payment' => 'succeeded',
                'subscription' => 'active',
                'entitlement' => $entitlement->fresh()->state,
            ],
            'query_count' => $queryCount,
            'processing_ms' => $elapsed,
            'evidence' => [
                'synthetic' => true,
                'provider_event_id' => $payload['event_id'],
                'provider_delivery_ids' => WebhookDelivery::query()->where('lab_run_id', $run->id)->pluck('provider_delivery_id')->all(),
                'entitlement_id' => $entitlement->id,
                'attempt_ids' => ProvisioningAttempt::query()->where('lab_run_id', $run->id)->pluck('id')->all(),
            ],
            'status' => $mode === 'fragile' ? 'failed' : 'completed',
            'completed_at' => $now,
        ]);

        return $run->fresh();
    }
}
