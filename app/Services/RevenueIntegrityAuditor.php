<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\AuditRun;
use App\Models\Incident;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RevenueIntegrityAuditor
{
    /** @return array{audit: AuditRun, detected: int, created: int, refreshed: int} */
    public function run(Workspace $workspace, ?User $initiator = null, ?CarbonImmutable $at = null): array
    {
        $startedAt = $at ?? now()->toImmutable();
        $timer = hrtime(true);

        return DB::transaction(function () use ($workspace, $initiator, $startedAt, $timer): array {
            $subscriptions = Subscription::query()
                ->whereBelongsTo($workspace)
                ->with([
                    'account',
                    'invoices' => fn ($query) => $query
                        ->whereDate('period_start', $startedAt->startOfMonth()->toDateString())
                        ->with('payments'),
                ])
                ->get();

            $findings = $this->detect($subscriptions, $startedAt);
            $created = 0;

            foreach ($findings as $finding) {
                $incident = Incident::query()->firstOrNew([
                    'workspace_id' => $workspace->id,
                    'fingerprint' => $finding['fingerprint'],
                ]);

                if (! $incident->exists) {
                    $incident->status = 'open';
                    $incident->detected_at = $startedAt;
                    $created++;
                }

                $incident->fill($finding);
                $incident->last_detected_at = $startedAt;
                $incident->save();
            }

            $recordsScanned = $subscriptions->count()
                + $subscriptions->sum(fn (Subscription $subscription): int => $subscription->invoices->count())
                + $subscriptions->sum(fn (Subscription $subscription): int => $subscription->invoices->sum(fn ($invoice): int => $invoice->payments->count()));

            $audit = AuditRun::query()->create([
                'workspace_id' => $workspace->id,
                'initiated_by' => $initiator?->id,
                'status' => 'completed',
                'records_scanned' => $recordsScanned,
                'findings_count' => count($findings),
                'new_incidents_count' => $created,
                'duration_ms' => max(1, (int) ((hrtime(true) - $timer) / 1_000_000)),
                'started_at' => $startedAt,
                'completed_at' => $startedAt,
            ]);

            Activity::query()->create([
                'workspace_id' => $workspace->id,
                'user_id' => $initiator?->id,
                'type' => 'audit_completed',
                'message' => sprintf('Integrity scan completed: %d anomalies detected, %d new.', count($findings), $created),
                'context' => ['audit_id' => $audit->id],
            ]);

            return [
                'audit' => $audit,
                'detected' => count($findings),
                'created' => $created,
                'refreshed' => count($findings) - $created,
            ];
        });
    }

    /**
     * @param  Collection<int, Subscription>  $subscriptions
     * @return list<array<string, mixed>>
     */
    public function detect(Collection $subscriptions, CarbonImmutable $at): array
    {
        $findings = [];

        foreach ($subscriptions as $subscription) {
            $account = $subscription->account;
            $invoices = $subscription->invoices->where('status', '!=', 'void')->values();

            if ($subscription->status === 'canceled') {
                foreach ($invoices as $invoice) {
                    $findings[] = $this->finding(
                        type: 'canceled_billing',
                        severity: 'high',
                        subscription: $subscription,
                        invoiceIds: [$invoice->id],
                        impact: (float) $invoice->amount,
                        title: 'Invoice issued after cancellation',
                        reason: sprintf('%s was billed %s %s after its subscription ended.', $account->name, $invoice->currency, number_format((float) $invoice->amount)),
                        action: 'Void the invoice, confirm the cancellation date, and notify the account owner.',
                    );
                }

                continue;
            }

            if ($subscription->status !== 'active') {
                continue;
            }

            if ($invoices->isEmpty()) {
                $findings[] = $this->finding(
                    type: 'missing_invoice',
                    severity: 'high',
                    subscription: $subscription,
                    invoiceIds: [],
                    impact: (float) $subscription->expected_monthly_amount,
                    title: 'Active subscription has no invoice',
                    reason: sprintf('%s has no invoice for %s, leaving the full monthly contract value unbilled.', $account->name, $at->format('F Y')),
                    action: 'Generate the missing invoice and verify the billing schedule before collection.',
                );

                continue;
            }

            if ($invoices->count() > 1) {
                $findings[] = $this->finding(
                    type: 'duplicate_invoice',
                    severity: 'medium',
                    subscription: $subscription,
                    invoiceIds: $invoices->pluck('id')->all(),
                    impact: (float) $invoices->min('amount'),
                    title: 'Duplicate invoices detected',
                    reason: sprintf('%s has %d active invoices for the same billing period.', $account->name, $invoices->count()),
                    action: 'Void the duplicate before collection and review invoice creation idempotency.',
                );

                continue;
            }

            $invoice = $invoices->first();
            $expected = (float) $subscription->expected_monthly_amount;
            $billed = (float) $invoice->amount;

            if ($billed < $expected) {
                $gap = $expected - $billed;
                $findings[] = $this->finding(
                    type: 'underbilling',
                    severity: $gap >= 1000 ? 'high' : 'medium',
                    subscription: $subscription,
                    invoiceIds: [$invoice->id],
                    impact: $gap,
                    title: 'Invoice is below contract value',
                    reason: sprintf('%s was billed %s %s below its expected monthly amount.', $account->name, $invoice->currency, number_format($gap)),
                    action: 'Validate contract terms, then issue an adjustment invoice for the shortfall.',
                );
            }

            $succeeded = $invoice->payments->contains('status', 'succeeded');
            $failedPayment = $invoice->payments->firstWhere('status', 'failed');

            if ($failedPayment && ! $succeeded) {
                $findings[] = $this->finding(
                    type: 'payment_failure',
                    severity: 'critical',
                    subscription: $subscription,
                    invoiceIds: [$invoice->id],
                    impact: $billed,
                    title: 'High-value payment failed',
                    reason: sprintf("%s's latest collection attempt failed (%s).", $account->name, str_replace('_', ' ', $failedPayment->failure_code ?? 'unknown reason')),
                    action: 'Contact the billing owner and retry collection with an updated payment method.',
                );
            } elseif (! $succeeded && $invoice->due_date->isBefore($at->startOfDay())) {
                $findings[] = $this->finding(
                    type: 'overdue_invoice',
                    severity: 'medium',
                    subscription: $subscription,
                    invoiceIds: [$invoice->id],
                    impact: $billed,
                    title: 'Invoice is overdue without payment',
                    reason: sprintf("%s's invoice passed its due date with no successful payment.", $account->name),
                    action: 'Confirm invoice delivery, contact the billing owner, and schedule a collection retry.',
                );
            }
        }

        $weights = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
        usort($findings, fn (array $left, array $right): int => [$weights[$right['severity']], $right['revenue_impact']] <=> [$weights[$left['severity']], $left['revenue_impact']]
        );

        return $findings;
    }

    /** @param list<int> $invoiceIds @return array<string, mixed> */
    private function finding(
        string $type,
        string $severity,
        Subscription $subscription,
        array $invoiceIds,
        float $impact,
        string $title,
        string $reason,
        string $action,
    ): array {
        sort($invoiceIds);

        return [
            'workspace_id' => $subscription->workspace_id,
            'account_id' => $subscription->account_id,
            'subscription_id' => $subscription->id,
            'invoice_id' => $invoiceIds[0] ?? null,
            'fingerprint' => implode(':', [$type, $subscription->id, ...$invoiceIds]),
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'reason' => $reason,
            'recommended_action' => $action,
            'revenue_impact' => $impact,
            'currency' => $subscription->currency,
            'evidence' => ['invoice_ids' => $invoiceIds],
        ];
    }
}
