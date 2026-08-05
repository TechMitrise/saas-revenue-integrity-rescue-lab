<?php

namespace Tests\Feature;

use App\Models\Entitlement;
use App\Models\Incident;
use App\Models\LabRun;
use App\Models\ProvisioningAttempt;
use App\Models\ReconciliationAction;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEvent;
use App\Models\Workspace;
use App\Services\ReconciliationService;
use App\Services\RevenueIntegrityAuditor;
use App\Services\WebhookIngestor;
use App\Services\WebhookSignature;
use Carbon\CarbonImmutable;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaidAccessRescueTest extends TestCase
{
    use RefreshDatabase;

    private Workspace $workspace;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workspace = Workspace::query()->create([
            'name' => 'Paid Access Test Lab',
            'slug' => 'paid-access-test',
            'timezone' => 'UTC',
            'currency' => 'USD',
        ]);
        $this->user = User::query()->create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Test Operator',
            'email' => 'paid-access@example.test',
            'password' => Hash::make('password'),
            'role' => 'Revenue Operations',
        ]);
        resolve(DemoScenarioSeeder::class)->seed(
            $this->workspace,
            CarbonImmutable::parse('2026-08-05 14:00:00', 'UTC'),
        );
    }

    public function test_ten_deliveries_create_one_provider_event_transition_and_entitlement_per_run(): void
    {
        $fragile = $this->labRun('fragile');

        $this->assertSame(10, WebhookDelivery::query()->where('lab_run_id', $fragile->id)->count());
        $this->assertSame(1, WebhookEvent::query()->where('lab_run_id', $fragile->id)->count());
        $this->assertSame(1, (int) WebhookEvent::query()->where('lab_run_id', $fragile->id)->value('business_effects'));
        $this->assertSame(1, Entitlement::query()->where('lab_run_id', $fragile->id)->count());
        $this->assertSame('active', $fragile->subscription->fresh()->status);
    }

    public function test_out_of_order_and_invalid_events_cannot_corrupt_subscription_or_access(): void
    {
        $run = $this->labRun('fragile');
        $subscription = $run->subscription;
        $entitlement = $run->entitlements()->firstOrFail();
        $payload = [
            'event_id' => 'evt_out_of_order_cancel',
            'type' => 'subscription.canceled',
            'account_id' => $run->account->external_id,
            'subscription_id' => $subscription->external_id,
            'payment_id' => $run->payment->external_id,
            'amount' => 4800,
            'currency' => 'USD',
            'synthetic' => true,
        ];
        $signature = resolve(WebhookSignature::class)->sign($payload);
        $result = resolve(WebhookIngestor::class)->ingest($this->workspace, $payload, $signature, 'dlv_out_of_order', $run, false);
        $invalid = resolve(WebhookIngestor::class)->ingest(
            $this->workspace,
            [...$payload, 'event_id' => 'evt_invalid_signature'],
            'invalid-signature',
            'dlv_invalid_signature',
            $run,
            false,
        );

        $this->assertSame('rejected', $result['event']->status);
        $this->assertFalse($invalid['accepted']);
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertSame('locked', $entitlement->fresh()->state);
        $this->assertSame(1, Entitlement::query()->where('subscription_id', $subscription->id)->count());
    }

    public function test_provisioning_job_exhausts_exactly_three_attempts_and_records_failures(): void
    {
        $run = $this->labRun('fragile');
        $attempts = ProvisioningAttempt::query()->where('lab_run_id', $run->id)->orderBy('attempt_number')->get();

        $this->assertCount(3, $attempts);
        $this->assertSame([1, 2, 3], $attempts->pluck('attempt_number')->all());
        $this->assertSame(['failed'], $attempts->pluck('status')->unique()->values()->all());
        $this->assertSame(['synthetic_provider_timeout'], $attempts->pluck('failure_code')->unique()->values()->all());
        $this->assertSame('failed', $run->fresh()->status);
    }

    public function test_paid_but_locked_incident_and_sla_breach_are_detected_from_domain_state(): void
    {
        $result = resolve(RevenueIntegrityAuditor::class)->run($this->workspace, $this->user);
        $incident = Incident::query()->where('workspace_id', $this->workspace->id)->firstOrFail();

        $this->assertSame(1, $result['detected']);
        $this->assertSame(0, $result['created']);
        $this->assertSame('paid_but_locked', $incident->type);
        $this->assertSame('locked', $incident->evidence['entitlement_state']);
        $this->assertGreaterThan($incident->evidence['sla_threshold_ms'], $incident->evidence['payment_to_access_ms']);
        $this->assertNotNull($incident->sla_breached_at);
    }

    public function test_reconciliation_repairs_once_and_repeat_is_an_audited_no_op(): void
    {
        $incident = Incident::query()->where('workspace_id', $this->workspace->id)->firstOrFail();
        $service = resolve(ReconciliationService::class);
        $first = $service->reconcile($incident, $this->user);
        $second = $service->reconcile($incident->fresh(), $this->user);

        $this->assertSame('repaired', $first->result);
        $this->assertSame(1, $first->business_effects);
        $this->assertSame('locked', $first->before_state['entitlement_state']);
        $this->assertSame('active', $first->after_state['entitlement_state']);
        $this->assertSame('no_op', $second->result);
        $this->assertSame(0, $second->business_effects);
        $this->assertSame(1, $second->after_state['entitlement_count']);
        $this->assertSame(1, (int) ReconciliationAction::query()->sum('business_effects'));
        $this->assertSame(1, Entitlement::query()->where('subscription_id', $incident->subscription_id)->count());
        $this->assertSame('recovered', $incident->fresh()->status);
    }

    public function test_revenue_impact_uses_the_paid_subscription_monthly_amount(): void
    {
        $incident = Incident::query()->where('workspace_id', $this->workspace->id)->firstOrFail();
        $run = $this->labRun('fragile');

        $this->assertSame('succeeded', $run->payment->status);
        $this->assertSame(4800.0, (float) $run->subscription->expected_monthly_amount);
        $this->assertSame((float) $run->subscription->expected_monthly_amount, (float) $incident->revenue_impact);

        $this->actingAs($this->user)->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('metrics.revenueAtRisk', 4800)
            ->assertJsonPath('incident.revenueFormula', 'Synthetic monthly revenue exposed = the active paid subscription monthly amount while access remains locked.');
    }

    public function test_dashboard_exposes_fragile_and_stabilised_reproducible_evidence(): void
    {
        $this->actingAs($this->user)->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('comparison.0.mode', 'fragile')
            ->assertJsonPath('comparison.0.webhookDeliveries', 10)
            ->assertJsonPath('comparison.0.queueFailures', 3)
            ->assertJsonPath('comparison.0.entitlementState', 'locked')
            ->assertJsonPath('comparison.0.slaBreached', true)
            ->assertJsonPath('comparison.1.mode', 'stabilised')
            ->assertJsonPath('comparison.1.webhookDeliveries', 10)
            ->assertJsonPath('comparison.1.providerEvents', 1)
            ->assertJsonPath('comparison.1.businessActions', 1)
            ->assertJsonPath('comparison.1.entitlementState', 'active')
            ->assertJsonPath('comparison.1.slaBreached', false);
    }

    public function test_incident_reconciliation_is_isolated_by_workspace(): void
    {
        $otherWorkspace = Workspace::query()->create([
            'name' => 'Other Workspace', 'slug' => 'other-workspace', 'timezone' => 'UTC', 'currency' => 'USD',
        ]);
        $otherUser = User::query()->create([
            'workspace_id' => $otherWorkspace->id,
            'name' => 'Other Operator',
            'email' => 'other@example.test',
            'password' => Hash::make('password'),
            'role' => 'Revenue Operations',
        ]);
        $incident = Incident::query()->where('workspace_id', $this->workspace->id)->firstOrFail();

        $this->actingAs($otherUser)
            ->postJson("/api/incidents/{$incident->id}/reconcile")
            ->assertNotFound();

        $this->assertSame('open', $incident->fresh()->status);
    }

    public function test_scenario_reset_is_idempotent_and_restores_the_active_incident(): void
    {
        $incident = Incident::query()->where('workspace_id', $this->workspace->id)->firstOrFail();
        resolve(ReconciliationService::class)->reconcile($incident, $this->user);

        $this->actingAs($this->user)->postJson('/api/demo/reset')
            ->assertOk()
            ->assertJsonPath('dashboard.metrics.status', 'open')
            ->assertJsonPath('dashboard.metrics.accessState', 'locked')
            ->assertJsonPath('dashboard.metrics.duplicateDeliveries', 9)
            ->assertJsonCount(2, 'dashboard.comparison');

        $this->assertSame(20, WebhookDelivery::query()->where('workspace_id', $this->workspace->id)->count());
        $this->assertSame(2, Entitlement::query()->where('workspace_id', $this->workspace->id)->count());
        $this->assertSame(1, Incident::query()->where('workspace_id', $this->workspace->id)->count());
    }

    public function test_guided_demo_persists_each_incident_and_recovery_step(): void
    {
        $this->actingAs($this->user);

        $this->postJson('/api/demo/guided/start')
            ->assertOk()
            ->assertJsonPath('dashboard.guide.active', true)
            ->assertJsonPath('dashboard.guide.completed', 1)
            ->assertJsonPath('dashboard.guide.facts.deliveries', 0)
            ->assertJsonPath('dashboard.incident', null);

        $run = LabRun::query()
            ->where('workspace_id', $this->workspace->id)
            ->where('mode', 'fragile')
            ->firstOrFail();
        $this->assertSame('payment_succeeded', $run->status);
        $this->assertSame(0, $run->deliveries()->count());
        $this->assertSame(0, $run->entitlements()->count());

        $this->postJson('/api/demo/guided/advance')
            ->assertOk()
            ->assertJsonPath('dashboard.guide.completed', 2)
            ->assertJsonPath('dashboard.guide.facts.deliveries', 10)
            ->assertJsonPath('dashboard.guide.facts.events', 1)
            ->assertJsonPath('dashboard.guide.facts.businessActions', 1)
            ->assertJsonPath('dashboard.guide.facts.access', 'locked');
        $this->assertSame(1, WebhookEvent::query()->where('lab_run_id', $run->id)->count());
        $this->assertSame(1, Entitlement::query()->where('lab_run_id', $run->id)->count());

        foreach ([1, 2, 3] as $attemptNumber) {
            $this->postJson('/api/demo/guided/advance')
                ->assertOk()
                ->assertJsonPath('dashboard.guide.completed', 2 + $attemptNumber)
                ->assertJsonPath('dashboard.guide.facts.queueFailures', $attemptNumber);
        }
        $this->assertSame(3, ProvisioningAttempt::query()->where('lab_run_id', $run->id)->count());

        $this->postJson('/api/demo/guided/advance')
            ->assertOk()
            ->assertJsonPath('dashboard.guide.completed', 6)
            ->assertJsonPath('dashboard.guide.facts.incident', 'open')
            ->assertJsonPath('dashboard.guide.facts.revenue', 4800);
        $incident = Incident::query()->where('lab_run_id', $run->id)->firstOrFail();
        $this->assertSame('paid_but_locked', $incident->type);

        $this->postJson('/api/demo/guided/advance')
            ->assertOk()
            ->assertJsonPath('dashboard.guide.completed', 7)
            ->assertJsonPath('dashboard.guide.facts.access', 'active')
            ->assertJsonPath('dashboard.guide.facts.lastResult', 'repaired');

        $this->postJson('/api/demo/guided/advance')
            ->assertOk()
            ->assertJsonPath('dashboard.guide.complete', true)
            ->assertJsonPath('dashboard.guide.completed', 8)
            ->assertJsonPath('dashboard.guide.facts.lastResult', 'no_op');
        $this->assertSame(2, ReconciliationAction::query()->where('lab_run_id', $run->id)->count());
        $this->assertSame(1, (int) ReconciliationAction::query()->where('lab_run_id', $run->id)->sum('business_effects'));
        $this->assertSame('recovered', $incident->fresh()->status);
    }

    private function labRun(string $mode): LabRun
    {
        return LabRun::query()
            ->where('workspace_id', $this->workspace->id)
            ->where('mode', $mode)
            ->with(['account', 'subscription', 'payment', 'entitlements'])
            ->firstOrFail();
    }
}
