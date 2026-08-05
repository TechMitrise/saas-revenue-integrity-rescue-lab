<?php

namespace Tests\Unit;

use App\Models\AuditRun;
use App\Models\Incident;
use App\Models\User;
use App\Models\Workspace;
use App\Services\RevenueIntegrityAuditor;
use Carbon\CarbonImmutable;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RevenueIntegrityAuditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_detects_every_seeded_revenue_failure_mode(): void
    {
        [$workspace, $user, $at] = $this->scenario();

        $result = resolve(RevenueIntegrityAuditor::class)->run($workspace, $user, $at);

        $this->assertSame(6, $result['detected']);
        $this->assertSame(6, $result['created']);
        $this->assertEqualsCanonicalizing([
            'canceled_billing',
            'duplicate_invoice',
            'missing_invoice',
            'overdue_invoice',
            'payment_failure',
            'underbilling',
        ], Incident::query()->pluck('type')->all());
        $this->assertSame(12200.0, (float) Incident::query()->sum('revenue_impact'));
    }

    public function test_repeated_scans_refresh_incidents_without_duplicates(): void
    {
        [$workspace, $user, $at] = $this->scenario();
        $auditor = resolve(RevenueIntegrityAuditor::class);

        $auditor->run($workspace, $user, $at);
        $result = $auditor->run($workspace, $user, $at->addMinute());

        $this->assertSame(0, $result['created']);
        $this->assertSame(6, $result['refreshed']);
        $this->assertSame(6, Incident::query()->count());
        $this->assertSame(2, AuditRun::query()->count());
    }

    /** @return array{Workspace, User, CarbonImmutable} */
    private function scenario(): array
    {
        $at = CarbonImmutable::parse('2026-08-05 14:00:00', 'UTC');
        $workspace = Workspace::query()->create([
            'name' => 'Test Rescue Lab',
            'slug' => 'test-rescue-lab',
            'timezone' => 'UTC',
            'currency' => 'USD',
        ]);
        $user = User::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Test Operator',
            'email' => 'operator@example.test',
            'password' => Hash::make('password'),
            'role' => 'Revenue Operations',
        ]);
        resolve(DemoScenarioSeeder::class)->seed($workspace, $at);

        return [$workspace, $user, $at];
    }
}
