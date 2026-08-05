<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\User;
use App\Models\Workspace;
use App\Services\RevenueIntegrityAuditor;
use Carbon\CarbonImmutable;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RevenueRescueWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Workspace $workspace;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $at = CarbonImmutable::parse('2026-08-05 14:00:00', 'UTC');
        $this->workspace = Workspace::query()->create([
            'name' => 'Workflow Lab', 'slug' => 'workflow-lab', 'timezone' => 'UTC', 'currency' => 'USD',
        ]);
        $this->user = User::query()->create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Test Operator',
            'email' => 'workflow@example.test',
            'password' => Hash::make('password'),
            'role' => 'Revenue Operations',
        ]);
        resolve(DemoScenarioSeeder::class)->seed($this->workspace, $at);
        resolve(RevenueIntegrityAuditor::class)->run($this->workspace, $this->user, $at);
    }

    public function test_dashboard_returns_the_workspace_risk_profile(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('metrics.totalMrr', 18900)
            ->assertJsonPath('metrics.atRisk', 12200)
            ->assertJsonPath('metrics.activeIncidents', 6)
            ->assertJsonCount(6, 'incidents');
    }

    public function test_operator_can_investigate_recover_and_note_an_incident(): void
    {
        $incident = Incident::query()->where('type', 'payment_failure')->firstOrFail();

        $this->actingAs($this->user)
            ->patchJson("/api/incidents/{$incident->id}", ['status' => 'investigating', 'assignee' => 'Maya Chen'])
            ->assertOk()
            ->assertJsonPath('incident.status', 'investigating')
            ->assertJsonPath('incident.assignee', 'Maya Chen');

        $this->postJson("/api/incidents/{$incident->id}/notes", ['body' => 'Billing owner confirmed a card update.'])
            ->assertCreated()
            ->assertJsonPath('incident.notes.0.body', 'Billing owner confirmed a card update.');

        $this->patchJson("/api/incidents/{$incident->id}", ['status' => 'recovered'])
            ->assertOk()
            ->assertJsonPath('incident.status', 'recovered');

        $this->getJson('/api/dashboard')
            ->assertJsonPath('metrics.atRisk', 7400)
            ->assertJsonPath('metrics.recovered', 4800)
            ->assertJsonPath('metrics.activeIncidents', 5);
    }

    public function test_incident_mutations_are_isolated_by_workspace(): void
    {
        $otherWorkspace = Workspace::query()->create([
            'name' => 'Other Workspace', 'slug' => 'other-workspace', 'timezone' => 'UTC', 'currency' => 'USD',
        ]);
        $otherUser = User::query()->create([
            'workspace_id' => $otherWorkspace->id,
            'name' => 'Other Operator',
            'email' => 'other@example.test',
            'password' => Hash::make('password'),
            'role' => 'Analyst',
        ]);
        $incident = Incident::query()->where('workspace_id', $this->workspace->id)->firstOrFail();

        $this->actingAs($otherUser)
            ->patchJson("/api/incidents/{$incident->id}", ['status' => 'dismissed'])
            ->assertNotFound();
    }

    public function test_scan_endpoint_is_idempotent_and_export_is_downloadable(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/audits')
            ->assertOk()
            ->assertJsonPath('summary.detected', 6)
            ->assertJsonPath('summary.created', 0);

        $this->get('/api/incidents/export.csv')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertDownload('revenue-integrity-incidents.csv');
    }

    public function test_demo_reset_restores_the_original_scenario(): void
    {
        $incident = Incident::query()->firstOrFail();
        $this->actingAs($this->user)->patchJson("/api/incidents/{$incident->id}", ['status' => 'recovered']);

        $this->postJson('/api/demo/reset')
            ->assertOk()
            ->assertJsonPath('dashboard.metrics.atRisk', 12200)
            ->assertJsonPath('dashboard.metrics.recovered', 0)
            ->assertJsonCount(6, 'dashboard.incidents');
    }
}
