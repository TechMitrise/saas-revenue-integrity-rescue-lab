<?php

namespace Tests\Unit;

use App\Models\Workspace;
use App\Services\XRayReportService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XRayReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private array $report;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $workspace = Workspace::query()->where('slug', 'rescue-lab')->firstOrFail();
        $this->report = resolve(XRayReportService::class)->report($workspace);
    }

    public function test_report_validation_references_and_timeline_integrity_pass(): void
    {
        $errors = resolve(XRayReportService::class)->validate($this->report);

        $this->assertSame([], $errors);
        $this->assertNotEmpty($this->report['evidence']);
        $this->assertSame(
            collect($this->report['timeline'])->pluck('at')->all(),
            collect($this->report['timeline'])->sortBy('at')->pluck('at')->values()->all(),
        );
    }

    public function test_every_critical_path_stage_has_expected_observed_state_and_evidence(): void
    {
        foreach ($this->report['stages'] as $stage) {
            $this->assertNotEmpty($stage['expected'], $stage['id']);
            $this->assertNotEmpty($stage['observed'], $stage['id']);
            $this->assertNotEmpty($stage['status'], $stage['id']);
            $this->assertNotEmpty($stage['evidence'], $stage['id']);
        }

        $this->assertSame('queue', collect($this->report['stages'])->firstWhere('status', 'diverged')['id']);
    }

    public function test_pricing_and_roadmap_totals_are_consistent(): void
    {
        $this->assertSame(2250, $this->report['pricing']['xray']);
        $this->assertSame(9500, $this->report['pricing']['sprint']);
        $this->assertSame(9500, collect($this->report['roadmap']['phases'])->sum('price'));
    }

    public function test_final_verified_state_and_idempotency_claim_are_supported(): void
    {
        $claims = collect($this->report['verifiedOutcome'])->pluck('claim')->all();

        $this->assertContains('Entitlement becomes active once', $claims);
        $this->assertContains('Repeat recovery is a no-op', $claims);
        $this->assertSame('EV-RECOVERY', collect($this->report['verifiedOutcome'])->firstWhere('claim', 'Repeat recovery is a no-op')['evidence']);
        $this->assertStringContainsString('no_op / 0 effects', collect($this->report['evidence'])->firstWhere('id', 'EV-RECOVERY')['value']);
    }

    public function test_findings_use_transparent_classifications_and_evidence(): void
    {
        foreach ($this->report['findings'] as $finding) {
            $this->assertContains($finding['classification'], [
                'confirmed defect',
                'systemic control gap',
                'confirmed risk controlled in Lab',
                'unverified risk',
                'improvement opportunity',
            ]);
            $this->assertNotEmpty($finding['acceptance']);
            $this->assertNotEmpty($finding['evidence']);
        }
    }
}
