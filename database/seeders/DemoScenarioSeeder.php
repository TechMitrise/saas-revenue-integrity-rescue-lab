<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Activity;
use App\Models\AuditRun;
use App\Models\Incident;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class DemoScenarioSeeder
{
    public function seed(Workspace $workspace, ?CarbonImmutable $at = null): void
    {
        $now = $at ?? now()->toImmutable();

        DB::transaction(function () use ($workspace, $now): void {
            Activity::query()->where('workspace_id', $workspace->id)->delete();
            AuditRun::query()->where('workspace_id', $workspace->id)->delete();
            Incident::query()->where('workspace_id', $workspace->id)->delete();
            Payment::query()->where('workspace_id', $workspace->id)->delete();
            Invoice::query()->where('workspace_id', $workspace->id)->delete();
            Subscription::query()->where('workspace_id', $workspace->id)->delete();
            Account::query()->where('workspace_id', $workspace->id)->delete();

            $accountData = [
                ['external_id' => 'acct_aperture', 'name' => 'Aperture Health', 'segment' => 'Enterprise', 'owner_name' => 'Maya Chen'],
                ['external_id' => 'acct_northstar', 'name' => 'Northstar Labs', 'segment' => 'Enterprise', 'owner_name' => 'Eli Turner'],
                ['external_id' => 'acct_orbit', 'name' => 'Orbit Freight', 'segment' => 'Growth', 'owner_name' => 'Maya Chen'],
                ['external_id' => 'acct_harbor', 'name' => 'Harbor & Pine', 'segment' => 'Growth', 'owner_name' => 'Noah Williams'],
                ['external_id' => 'acct_lumen', 'name' => 'Lumen Works', 'segment' => 'Growth', 'owner_name' => 'Eli Turner'],
                ['external_id' => 'acct_summit', 'name' => 'Summit AI', 'segment' => 'Startup', 'owner_name' => 'Noah Williams'],
                ['external_id' => 'acct_pioneer', 'name' => 'Pioneer Cloud', 'segment' => 'Startup', 'owner_name' => 'Maya Chen'],
                ['external_id' => 'acct_cedar', 'name' => 'Cedar Systems', 'segment' => 'Startup', 'owner_name' => 'Eli Turner'],
            ];

            $accounts = collect($accountData)->mapWithKeys(function (array $data) use ($workspace): array {
                $account = Account::query()->create(['workspace_id' => $workspace->id, ...$data]);

                return [$data['external_id'] => $account];
            });

            $subscriptionData = [
                ['external_id' => 'sub_aperture', 'account' => 'acct_aperture', 'plan' => 'Scale', 'status' => 'active', 'amount' => 6200],
                ['external_id' => 'sub_northstar', 'account' => 'acct_northstar', 'plan' => 'Scale', 'status' => 'active', 'amount' => 4800],
                ['external_id' => 'sub_orbit', 'account' => 'acct_orbit', 'plan' => 'Growth', 'status' => 'active', 'amount' => 3200],
                ['external_id' => 'sub_harbor', 'account' => 'acct_harbor', 'plan' => 'Growth', 'status' => 'active', 'amount' => 1800],
                ['external_id' => 'sub_lumen', 'account' => 'acct_lumen', 'plan' => 'Growth', 'status' => 'canceled', 'amount' => 2600],
                ['external_id' => 'sub_summit', 'account' => 'acct_summit', 'plan' => 'Launch', 'status' => 'active', 'amount' => 1250],
                ['external_id' => 'sub_pioneer', 'account' => 'acct_pioneer', 'plan' => 'Launch', 'status' => 'active', 'amount' => 950],
                ['external_id' => 'sub_cedar', 'account' => 'acct_cedar', 'plan' => 'Launch', 'status' => 'active', 'amount' => 700],
            ];

            $subscriptions = collect($subscriptionData)->mapWithKeys(function (array $data) use ($workspace, $accounts, $now): array {
                $subscription = Subscription::query()->create([
                    'workspace_id' => $workspace->id,
                    'account_id' => $accounts[$data['account']]->id,
                    'external_id' => $data['external_id'],
                    'plan' => $data['plan'],
                    'status' => $data['status'],
                    'expected_monthly_amount' => $data['amount'],
                    'currency' => 'USD',
                    'started_at' => $now->subYear()->startOfMonth(),
                    'canceled_at' => $data['status'] === 'canceled' ? $now->subDays(15) : null,
                ]);

                return [$data['external_id'] => $subscription];
            });

            $invoiceData = [
                ['external_id' => 'inv_aperture', 'subscription' => 'sub_aperture', 'amount' => 6200, 'status' => 'paid', 'due' => 'future'],
                ['external_id' => 'inv_northstar', 'subscription' => 'sub_northstar', 'amount' => 4800, 'status' => 'past_due', 'due' => 'past'],
                ['external_id' => 'inv_orbit', 'subscription' => 'sub_orbit', 'amount' => 2400, 'status' => 'paid', 'due' => 'future'],
                ['external_id' => 'inv_lumen', 'subscription' => 'sub_lumen', 'amount' => 2600, 'status' => 'open', 'due' => 'future'],
                ['external_id' => 'inv_summit', 'subscription' => 'sub_summit', 'amount' => 1250, 'status' => 'past_due', 'due' => 'past'],
                ['external_id' => 'inv_pioneer_a', 'subscription' => 'sub_pioneer', 'amount' => 950, 'status' => 'open', 'due' => 'future'],
                ['external_id' => 'inv_pioneer_b', 'subscription' => 'sub_pioneer', 'amount' => 950, 'status' => 'open', 'due' => 'future'],
                ['external_id' => 'inv_cedar', 'subscription' => 'sub_cedar', 'amount' => 700, 'status' => 'paid', 'due' => 'future'],
            ];

            $invoices = collect($invoiceData)->mapWithKeys(function (array $data) use ($workspace, $subscriptions, $now): array {
                $subscription = $subscriptions[$data['subscription']];
                $invoice = Invoice::query()->create([
                    'workspace_id' => $workspace->id,
                    'account_id' => $subscription->account_id,
                    'subscription_id' => $subscription->id,
                    'external_id' => $data['external_id'],
                    'period_start' => $now->startOfMonth(),
                    'period_end' => $now->endOfMonth(),
                    'amount' => $data['amount'],
                    'currency' => 'USD',
                    'status' => $data['status'],
                    'due_date' => $data['due'] === 'past' ? $now->subDays(2) : $now->addDays(7),
                ]);

                return [$data['external_id'] => $invoice];
            });

            foreach ([
                ['external_id' => 'pay_aperture', 'invoice' => 'inv_aperture', 'amount' => 6200, 'status' => 'succeeded'],
                ['external_id' => 'pay_northstar', 'invoice' => 'inv_northstar', 'amount' => 4800, 'status' => 'failed', 'failure_code' => 'insufficient_funds'],
                ['external_id' => 'pay_orbit', 'invoice' => 'inv_orbit', 'amount' => 2400, 'status' => 'succeeded'],
                ['external_id' => 'pay_cedar', 'invoice' => 'inv_cedar', 'amount' => 700, 'status' => 'succeeded'],
            ] as $data) {
                Payment::query()->create([
                    'workspace_id' => $workspace->id,
                    'invoice_id' => $invoices[$data['invoice']]->id,
                    'external_id' => $data['external_id'],
                    'amount' => $data['amount'],
                    'status' => $data['status'],
                    'attempted_at' => $now->subDay(),
                    'failure_code' => $data['failure_code'] ?? null,
                ]);
            }
        });
    }
}
