<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\LabRun;
use App\Models\ProvisioningAttempt;
use App\Models\Workspace;
use Illuminate\Support\Collection;
use RuntimeException;

class XRayReportService
{
    /** @return array<string, mixed> */
    public function report(Workspace $workspace): array
    {
        $runs = LabRun::query()
            ->whereBelongsTo($workspace)
            ->with(['account', 'subscription', 'payment', 'deliveries', 'events', 'attempts', 'entitlements', 'reconciliations'])
            ->orderByRaw("FIELD(mode, 'fragile', 'stabilised')")
            ->get();
        $fragile = $runs->firstWhere('mode', 'fragile') ?? throw new RuntimeException('Fragile Rescue Lab run is missing.');
        $stabilised = $runs->firstWhere('mode', 'stabilised') ?? throw new RuntimeException('Stabilised Rescue Lab run is missing.');
        $incident = Incident::query()
            ->whereBelongsTo($workspace)
            ->where('type', 'paid_but_locked')
            ->with('account')
            ->first() ?? throw new RuntimeException('Paid-but-locked incident is missing.');
        $payment = $fragile->payment;
        $event = $fragile->events->first();
        $entitlement = $fragile->entitlements->first();
        $attempts = $fragile->attempts->sortBy('attempt_number')->values();
        $ctaUrl = $this->approvedCtaUrl();

        $evidence = [
            ['id' => 'EV-PAYMENT', 'type' => 'observed', 'label' => 'Payment confirmation', 'source' => 'payments', 'value' => "{$payment->external_id} / {$payment->status} / USD ".number_format((float) $payment->amount), 'confidence' => 'High'],
            ['id' => 'EV-WEBHOOK', 'type' => 'observed', 'label' => 'Signed webhook inbox', 'source' => 'webhook_events + webhook_deliveries', 'value' => ($event?->provider_event_id ?? 'missing').' / '.$fragile->webhook_deliveries.' deliveries / '.$fragile->events->count().' event', 'confidence' => 'High'],
            ['id' => 'EV-QUEUE', 'type' => 'observed', 'label' => 'Provisioning attempts', 'source' => 'provisioning_attempts', 'value' => $attempts->count().' attempts / '.$fragile->queue_failures.' failures', 'confidence' => 'High'],
            ['id' => 'EV-ENTITLEMENT', 'type' => 'observed', 'label' => 'Entitlement state', 'source' => 'entitlements', 'value' => 'Entitlement '.$entitlement?->id.' / '.$entitlement?->state, 'confidence' => 'High'],
            ['id' => 'EV-INCIDENT', 'type' => 'observed', 'label' => 'SLA incident', 'source' => 'incidents', 'value' => "{$incident->type} / {$incident->status} / USD ".number_format((float) $incident->revenue_impact), 'confidence' => 'High'],
            ['id' => 'EV-RECOVERY', 'type' => 'verified', 'label' => 'Recovery design', 'source' => 'ReconciliationService + PaidAccessRescueTest', 'value' => 'Locked entitlement -> active once; repeat recovery -> no_op / 0 effects', 'confidence' => 'High'],
            ['id' => 'EV-STABILISED', 'type' => 'observed', 'label' => 'Stabilised replay', 'source' => 'lab_runs', 'value' => $stabilised->webhook_deliveries.' deliveries / '.$stabilised->business_actions.' action / '.$stabilised->entitlement_state.' access', 'confidence' => 'High'],
            ['id' => 'EV-UNKNOWN', 'type' => 'unknown', 'label' => 'Client-specific wider exposure', 'source' => 'real X-Ray scope', 'value' => 'Requires approved client evidence; not inferred from this Lab', 'confidence' => 'Explicit unknown'],
        ];

        $stages = [
            $this->stage('payment', 'Payment', 'Payment succeeds and remains authoritative.', "Payment {$payment->status}; USD ".number_format((float) $payment->amount).' synthetic monthly amount.', 'healthy', ['EV-PAYMENT'], 'Do not rewrite payment history to hide internal fulfilment gaps.', 'Incorrect rollback could corrupt the source-of-truth cash record.'),
            $this->stage('webhook', 'Webhook', 'At-least-once provider delivery is accepted safely.', $fragile->webhook_deliveries.' deliveries retained for one provider event.', 'healthy', ['EV-WEBHOOK'], 'Each provider event has one logical business effect.', 'Unsafe consumers can duplicate subscription or entitlement effects.'),
            $this->stage('processing', 'Event / processing layer', 'One accepted event advances subscription once.', $fragile->business_actions.' business action recorded.', 'healthy', ['EV-WEBHOOK'], 'Repeated delivery must not create repeated business effects.', 'Hidden replay behaviour can amplify later recovery work.'),
            $this->stage('queue', 'Queue / job', 'Entitlement provisioning succeeds within bounded attempts.', $fragile->queue_failures.' failed attempts; deterministic timeout evidence retained.', 'diverged', ['EV-QUEUE'], 'Async fulfilment must either complete, expose failure or hand off to safe recovery.', 'This is the first proven divergence in the incident path.'),
            $this->stage('subscription', 'Subscription', 'Paid subscription and access state remain consistent.', 'Subscription active while entitlement stayed locked.', 'warning', ['EV-PAYMENT', 'EV-ENTITLEMENT'], 'Active paid status alone does not prove customer value was delivered.', 'Commercial state can look healthy while the customer is blocked.'),
            $this->stage('entitlement', 'Entitlement', 'One active entitlement exists for the subscription/key.', 'One entitlement existed but state was locked.', 'diverged', ['EV-ENTITLEMENT'], 'Workspace/subscription/key uniqueness prevents duplicate access grants.', 'Manual repair without guards can create duplicate or conflicting entitlements.'),
            $this->stage('access', 'Customer access', 'Customer receives access within SLA.', 'Access remained locked for '.$this->duration($fragile->payment_to_access_ms).'.', 'blocked', ['EV-INCIDENT'], 'Payment-to-access SLA must surface paid-but-locked state.', 'The business consequence is a paid customer receiving no value after payment.'),
        ];

        $stateMatrix = [
            ['stage' => 'Payment', 'expected' => 'Succeeded payment remains authoritative', 'incident' => $payment->status, 'recovered' => $payment->status, 'evidence' => 'EV-PAYMENT', 'confidence' => 'High', 'implication' => 'Cash path was not the failure point.'],
            ['stage' => 'Webhook', 'expected' => 'Duplicate delivery retained without duplicate effect', 'incident' => '10 deliveries / 1 event / 1 effect', 'recovered' => 'No rewrite needed', 'evidence' => 'EV-WEBHOOK', 'confidence' => 'High', 'implication' => 'At-least-once input must be assumed.'],
            ['stage' => 'Queue', 'expected' => 'Provisioning completes or exposes bounded failure', 'incident' => '3 failures', 'recovered' => 'Handed to controlled reconciliation', 'evidence' => 'EV-QUEUE', 'confidence' => 'High', 'implication' => 'Retries alone did not deliver access.'],
            ['stage' => 'Subscription', 'expected' => 'Active paid subscription matches access', 'incident' => 'active subscription / locked access', 'recovered' => 'active subscription / active access', 'evidence' => 'EV-ENTITLEMENT', 'confidence' => 'High', 'implication' => 'State reconciliation is required before customer-impact decisions.'],
            ['stage' => 'Entitlement', 'expected' => 'One active entitlement', 'incident' => 'one locked entitlement', 'recovered' => 'one active entitlement', 'evidence' => 'EV-RECOVERY', 'confidence' => 'High', 'implication' => 'Recovery should update the existing entitlement.'],
            ['stage' => 'Remaining risk', 'expected' => 'Systemic exposure known', 'incident' => 'unknown outside fixture', 'recovered' => 'still unknown outside fixture', 'evidence' => 'EV-UNKNOWN', 'confidence' => 'Unknown', 'implication' => 'A real X-Ray checks whether this pattern exists elsewhere.'],
        ];

        $timeline = $this->timeline($fragile, $incident, $attempts);
        $rules = [
            $this->rule('BR-01', 'Successful payment does not independently prove access.', 'Protect customer-value delivery after cash collection.', 'EV-PAYMENT', 'RevenueIntegrityAuditor / dashboard evidence', 'High', 'Paid customer can remain locked without detection.', 'Engineering + Revenue Ops', 'Feature test for paid-but-locked detection.'),
            $this->rule('BR-02', 'Repeated provider delivery must not create duplicate business effects.', 'Protect subscription and entitlement state from replay.', 'EV-WEBHOOK', 'WebhookIngestor unique provider event and idempotent consumer', 'High', 'Duplicate entitlements or repeated transitions.', 'Platform Engineering', 'Duplicate delivery feature test.'),
            $this->rule('BR-03', 'Active paid status requires a consistent entitlement.', 'Align commercial state with delivered value.', 'EV-ENTITLEMENT', 'Auditor and entitlement constraints', 'High', 'Support tickets, churn risk and unsafe manual fixes.', 'Product Engineering', 'Expected-vs-actual reconciliation assertion.'),
            $this->rule('BR-04', 'Stale or invalid events must not overwrite newer state.', 'Prevent provider noise from corrupting lifecycle state.', 'EV-WEBHOOK', 'Signature verification and guarded transitions', 'High', 'Access can be cancelled or mutated by unsafe replay.', 'Platform Engineering', 'Out-of-order and invalid event tests.'),
            $this->rule('BR-05', 'Recovery requires audit, idempotency and verification.', 'Make repair safe to run under pressure.', 'EV-RECOVERY', 'ReconciliationService transaction and immutable actions', 'High', 'Manual repair can duplicate or hide the true incident.', 'Engineering Lead', 'Repeat no-op recovery test.'),
            $this->rule('BR-06', 'Rollback must not corrupt authoritative payment records.', 'Keep cash evidence separate from fulfilment repair.', 'EV-PAYMENT', 'Operations runbook', 'Specimen inference', 'Financial truth can be obscured by internal state rewrites.', 'Engineering + Finance owner', 'Rollback checklist and audit review.'),
        ];

        $findings = [
            $this->finding('F-01', 'Paid customer locked after successful payment', 'Critical', 'confirmed defect', 'Payment-to-access workflow', ['EV-PAYMENT', 'EV-ENTITLEMENT', 'EV-INCIDENT'], 'One synthetic paid account received no access beyond SLA.', 'Contain and reconcile affected paid subscriptions before broad changes.', 'Immediate', 'Query finds zero paid active subscriptions with locked entitlements beyond SLA.'),
            $this->finding('F-02', 'Async fulfilment exhausted retries without closure', 'High', 'systemic control gap', 'Queue / entitlement job', ['EV-QUEUE'], 'Retries were visible but did not complete access or trigger safe automatic closure.', 'Add bounded failure handling, alerting and operator handoff.', '24-48 hours', 'Exhausted jobs create actionable incident evidence.'),
            $this->finding('F-03', 'Manual repair needs repeat-safety proof', 'High', 'confirmed risk controlled in Lab', 'Reconciliation', ['EV-RECOVERY'], 'A duplicate repair path could create multiple entitlements or hide state divergence.', 'Use lock-protected reconciliation and run repeat no-op verification.', 'Before production repair', 'Second repair returns no_op and entitlement count remains one.'),
            $this->finding('F-04', 'Wider exposure is unknown outside the selected path', 'Medium', 'unverified risk', 'Portfolio / similar workflows', ['EV-UNKNOWN'], 'Other paid workflows may share the same hidden business rules.', 'Scope a real X-Ray against one selected client workflow.', 'Before modernisation scope', 'Approved evidence confirms or rejects similar exposure.'),
            $this->finding('F-05', 'Evidence standard should precede rewrite or AI automation', 'Medium', 'improvement opportunity', 'Modernisation decision', ['EV-STABILISED'], 'Unsafe automation can accelerate an unresolved state problem.', 'Modernise the critical path after evidence, tests and rollback are in place.', 'Before sprint planning', 'Critical-path map, tests and rollback rehearsal accepted.'),
        ];

        $containment = [
            $this->containment('Identify paid-but-locked accounts', 'Find affected subscriptions without mutating state.', 'Database access to payment, subscription and entitlement state.', 'Run scoped reconciliation query by workspace and SLA.', 'Engineering owner', 'Query result and account list.', 'False positives if SLA rule is wrong.', 'Read-only query can be discarded.', 'Sample accounts checked against payment and access state.', 'Does not prove wider workflows are healthy.'),
            $this->containment('Freeze broad replay/retry actions', 'Prevent duplicate effects during investigation.', 'Incident commander agrees recovery boundary.', 'Pause bulk queue retries and manual entitlement creation.', 'Engineering lead', 'Decision note and queue state.', 'May delay unrelated work.', 'Resume only after root cause and repair path are confirmed.', 'No new duplicate entitlements appear.', 'Does not fix the locked account.'),
            $this->containment('Run controlled recovery once', 'Restore access through the existing entitlement.', 'Payment, subscription and entitlement identifiers confirmed.', 'Execute transactionally scoped reconciliation.', 'Application engineer', 'Before/after snapshot and action ID.', 'Incorrect scope could touch wrong workspace.', 'Forward correction; do not rewrite payment history.', 'Access active; entitlement count one.', 'Only covers selected incident.'),
            $this->containment('Verify repeat no-op', 'Prove the repair is safe to run twice.', 'First recovery completed and audited.', 'Run the same reconciliation again.', 'Application engineer', 'No-op action ID and zero effects.', 'Hides defects if evidence not inspected.', 'Stop and investigate if effects are non-zero.', 'Second result is no_op; entitlement count remains one.', 'Does not remove need for root repair.'),
        ];

        $roadmap = [
            ['phase' => 'Containment and reconciliation', 'window' => 'Days 1-5', 'outcome' => 'Affected paid-access state is bounded and repaired safely.', 'deliverables' => ['paid-but-locked query', 'controlled recovery path', 'repeat no-op verification'], 'acceptance' => 'Known affected records reconciled; no duplicate entitlements created.', 'buyerResponsibility' => 'Provide approved access and name the workflow owner.', 'price' => 2500],
            ['phase' => 'Root repair and business-rule tests', 'window' => 'Days 6-12', 'outcome' => 'Hidden business rules are captured as executable controls.', 'deliverables' => ['Business Rule Atlas', 'feature tests', 'state-transition guardrails'], 'acceptance' => 'Tests fail on paid-but-locked and duplicate-effect regressions.', 'buyerResponsibility' => 'Confirm rule owners and SLA thresholds.', 'price' => 2500],
            ['phase' => 'Observability, idempotency and failure handling', 'window' => 'Days 13-21', 'outcome' => 'Retries, inbox state and recovery signals become operationally visible.', 'deliverables' => ['correlation trail', 'failure handoff', 'idempotency evidence'], 'acceptance' => 'A replayed provider event creates one business effect and visible evidence.', 'buyerResponsibility' => 'Approve operational alert thresholds.', 'price' => 2500],
            ['phase' => 'Controlled release, rollback rehearsal and handover', 'window' => 'Days 22-30', 'outcome' => 'The critical path can be changed without blind production risk.', 'deliverables' => ['release checklist', 'rollback rehearsal', 'engineering handover'], 'acceptance' => 'Release and rollback steps are rehearsed with evidence captured.', 'buyerResponsibility' => 'Schedule release window and responsible approver.', 'price' => 2000],
        ];

        $report = [
            'metadata' => [
                'title' => 'Critical Software X-Ray Specimen',
                'subtitle' => 'Revenue Access Failure',
                'route' => '/critical-software-xray/sample/revenue-access-failure',
                'preparedFor' => 'Qualified founders, CTOs, product leaders and engineering leaders',
                'marketCategory' => 'Software Rescue & Application Modernisation',
                'publicPromise' => 'Make critical software safer to run, change and automate.',
                'positioning' => 'Tech Mitrise rescues fragile, business-critical software, then modernises the critical path and adds controlled AI without putting the business at risk.',
                'belief' => 'Modernisation should not begin with a rewrite. AI should not begin with an agent. Both should begin with evidence.',
                'specimenLabel' => 'Synthetic professional-service specimen—not a client case study or production monitoring product.',
                'generatedAt' => now()->toIso8601String(),
            ],
            'boundary' => [
                'Synthetic demonstration based on the completed Rescue Lab incident.',
                'Not a real client case, testimonial or production incident report.',
                'Not live monitoring, legal advice, compliance assurance or financial assurance.',
                'Commercial scope and pricing are illustrative specimen values, not a quotation or guarantee.',
                'Reusable proprietary assessment internals are intentionally redacted.',
            ],
            'executiveBrief' => [
                'incidentTitle' => $incident->title,
                'workflow' => 'Payment -> webhook -> queue -> subscription -> entitlement -> customer access',
                'confirmedConsequence' => 'A synthetic paid subscription remained locked beyond its entitlement SLA.',
                'currentStatus' => 'Synthetic incident is reproducible; recovery design verified by Lab command/tests.',
                'severity' => 'Critical',
                'evidenceConfidence' => 'High for selected workflow; wider exposure unknown.',
                'divergence' => 'Queue/job provisioning failed three bounded attempts after payment and idempotent event processing succeeded.',
                'decisionRequired' => 'Contain paid-but-locked exposure and approve a controlled X-Ray before rewrite or automation.',
                'conclusion' => 'The payment path was healthy. Fulfilment and access-state reconciliation were the risk.',
                'confirmedImpact' => 'One synthetic account, USD '.number_format((float) $incident->revenue_impact).' monthly subscription amount exposed while locked.',
                'possibleWiderExposure' => 'Similar workflows that collect payment before asynchronous fulfilment may share the same hidden rules.',
                'unknowns' => 'A real client X-Ray must confirm code paths, data quality, ownership, monitoring and release constraints.',
            ],
            'pricing' => [
                'xray' => 2250,
                'sprint' => 9500,
                'creditWindow' => 'X-Ray fee may be credited if the sprint is signed within 14 days.',
                'label' => 'Illustrative specimen pricing-not a quotation or guarantee.',
            ],
            'evidence' => $evidence,
            'stages' => $stages,
            'stateMatrix' => $stateMatrix,
            'timeline' => $timeline,
            'rootCause' => [
                'proximateFailure' => 'The entitlement provisioning job exhausted bounded synthetic timeouts.',
                'underlyingGap' => 'The paid-access path needed evidence-backed detection and controlled reconciliation after async fulfilment failure.',
                'whyNotEarlier' => 'Payment and subscription state looked healthy until payment-to-access evidence was reconciled against entitlement state and SLA.',
                'supportingEvidence' => ['EV-PAYMENT', 'EV-WEBHOOK', 'EV-QUEUE', 'EV-ENTITLEMENT', 'EV-INCIDENT'],
                'competingExplanations' => [
                    ['explanation' => 'Payment provider failure', 'decision' => 'Rejected for this fixture: payment is succeeded and authoritative.', 'evidence' => 'EV-PAYMENT'],
                    ['explanation' => 'Duplicate webhook created duplicate customer effects', 'decision' => 'Rejected for this fixture: one event and one business effect were recorded.', 'evidence' => 'EV-WEBHOOK'],
                    ['explanation' => 'Access failed because no subscription existed', 'decision' => 'Rejected for this fixture: subscription became active while entitlement stayed locked.', 'evidence' => 'EV-ENTITLEMENT'],
                ],
                'unknowns' => 'Whether the same control gap exists in other client workflows requires approved client evidence.',
                'confidence' => 'High for the synthetic workflow; unknown beyond it.',
            ],
            'rules' => $rules,
            'severityModel' => [
                'Customer/revenue impact',
                'Likelihood or exposure',
                'Detectability',
                'Recoverability',
                'Evidence confidence',
            ],
            'findings' => $findings,
            'containment' => $containment,
            'doNotDoYet' => [
                'Do not rewrite the billing system before evidence is mapped.',
                'Do not blindly replay every provider event or failed job.',
                'Do not create replacement entitlements without uniqueness and audit.',
                'Do not edit payment-provider history to make internal state appear consistent.',
                'Do not add an AI agent to a workflow whose business rules are still hidden.',
            ],
            'recovery' => [
                'approvedAction' => 'Activate the existing locked entitlement through ReconciliationService.',
                'prerequisites' => 'Confirm workspace, payment, subscription, entitlement and incident identifiers.',
                'boundary' => 'One workspace, one incident, existing entitlement only.',
                'idempotency' => 'Unique entitlement plus row locks; repeat action returns no_op and zero effects.',
                'verification' => ['payment remains succeeded', 'subscription remains active', 'entitlement active', 'access restored', 'repaired action recorded', 'repeat no_op recorded'],
                'rollbackTrigger' => 'Unexpected workspace, entitlement count greater than one, repeat effect greater than zero, or mismatched payment/subscription evidence.',
                'rollbackAction' => 'Stop further repair and apply forward correction after evidence review; do not rewrite payment history.',
                'monitoring' => 'Watch new paid-but-locked incidents, failed provisioning attempts and duplicate-effect counts.',
                'owner' => 'Engineering lead with revenue owner approval.',
            ],
            'verifiedOutcome' => [
                ['claim' => 'Payment remains authoritative', 'evidence' => 'EV-PAYMENT', 'status' => 'verified in synthetic scenario'],
                ['claim' => 'Subscription state is correct', 'evidence' => 'EV-PAYMENT', 'status' => 'verified in synthetic scenario'],
                ['claim' => 'Entitlement becomes active once', 'evidence' => 'EV-RECOVERY', 'status' => 'verified by command/test'],
                ['claim' => 'Access is restored', 'evidence' => 'EV-RECOVERY', 'status' => 'verified in synthetic scenario'],
                ['claim' => 'Recovery is audited', 'evidence' => 'EV-RECOVERY', 'status' => 'verified by immutable action model/test'],
                ['claim' => 'Repeat recovery is a no-op', 'evidence' => 'EV-RECOVERY', 'status' => 'verified by command/test'],
                ['claim' => 'Systemic risks remain visible', 'evidence' => 'EV-UNKNOWN', 'status' => 'not production proven'],
            ],
            'roadmap' => [
                'total' => 9500,
                'phases' => $roadmap,
                'assumptions' => ['One selected critical workflow.', 'Approved access to relevant code and sanitized evidence.', 'No production credentials required for the first triage call.'],
                'exclusions' => ['Full rewrite.', 'Security/compliance certification.', 'Financial audit.', 'Broad codebase review outside selected path.', 'AI copilot or autonomous agent build.'],
                'accessRequired' => ['Repository or architecture context.', 'Sanitized events/logs when available.', 'Engineering and business owner availability.'],
                'changeControl' => 'Production changes require buyer approval, release window and rollback owner.',
                'deploymentResponsibility' => 'Buyer retains production deployment authority unless separately agreed.',
                'scopeChangeConditions' => ['Multiple workflows.', 'Missing evidence.', 'Required infrastructure change.', 'Security or compliance blockers.'],
                'nextDecision' => 'Approve the Critical Software X-Ray first; use its evidence to decide whether the 30-day sprint is responsible.',
            ],
            'deliverables' => [
                'Critical-path map',
                'Evidence and state reconciliation',
                'Business Rule Atlas sample',
                'Prioritised risks',
                'Immediate containment decision',
                'Recovery and rollback plan',
                'Baseline and verification criteria',
                'Priced 30-day rescue roadmap',
                'Executive readout',
            ],
            'guided' => [
                ['title' => 'Business failure', 'summary' => 'Payment succeeded, but customer access stayed locked beyond the entitlement SLA.', 'anchor' => 'executive-brief'],
                ['title' => 'First proven divergence', 'summary' => 'The Queue / job stage is the first place where expected and actual state split.', 'anchor' => 'critical-path'],
                ['title' => 'Evidence and root-cause decision', 'summary' => 'Payment and webhook replay safety held; async fulfilment failed to close the access gap.', 'anchor' => 'root-cause'],
                ['title' => 'Safe containment', 'summary' => 'Bound the affected paid-access state and pause broad retries or manual entitlement creation.', 'anchor' => 'containment'],
                ['title' => 'Approved recovery and rollback boundary', 'summary' => 'Repair the existing entitlement under locks, with rollback triggers defined before action.', 'anchor' => 'recovery'],
                ['title' => 'Verified synthetic outcome', 'summary' => 'Access becomes active once; repeat recovery is no_op with zero additional effects.', 'anchor' => 'verified-outcome'],
                ['title' => '$2,250 X-Ray and $9,500 roadmap', 'summary' => 'Buy the X-Ray first, then decide whether the four-phase 30-day rescue sprint is responsible.', 'anchor' => 'roadmap'],
                ['title' => 'Risk Triage CTA', 'summary' => 'Bring one critical workflow that is failing, fragile or too risky to change.', 'anchor' => 'access-required'],
            ],
            'cta' => [
                'label' => 'Request a 20-minute Critical Software Risk Triage',
                'url' => $ctaUrl,
                'supporting' => 'Bring one critical workflow that is failing, fragile or too risky to change. We will qualify the impact, urgency, evidence access and fit. The call is not a free architecture audit.',
            ],
        ];

        $this->validate($report);

        return $report;
    }

    /** @return array<int, string> */
    public function validate(array $report): array
    {
        $errors = [];
        $evidenceIds = collect($report['evidence'] ?? [])->pluck('id')->all();
        $knownEvidence = array_flip($evidenceIds);
        $checkRefs = function (array $refs, string $context) use (&$errors, $knownEvidence): void {
            foreach ($refs as $ref) {
                if (! isset($knownEvidence[$ref])) {
                    $errors[] = "{$context} references missing evidence {$ref}.";
                }
            }
        };

        foreach ($report['stages'] ?? [] as $stage) {
            foreach (['expected', 'observed', 'status'] as $key) {
                if (blank($stage[$key] ?? null)) {
                    $errors[] = "Stage {$stage['id']} is missing {$key}.";
                }
            }
            $checkRefs($stage['evidence'] ?? [], "Stage {$stage['id']}");
        }

        foreach ($report['stateMatrix'] ?? [] as $row) {
            $checkRefs([$row['evidence'] ?? ''], "State row {$row['stage']}");
        }

        foreach ($report['timeline'] ?? [] as $event) {
            $checkRefs([$event['evidence'] ?? ''], "Timeline event {$event['id']}");
        }

        foreach ($report['findings'] ?? [] as $finding) {
            if (($finding['classification'] ?? '') !== 'unverified risk' && empty($finding['evidence'])) {
                $errors[] = "Finding {$finding['id']} has no evidence.";
            }
            $checkRefs($finding['evidence'] ?? [], "Finding {$finding['id']}");
        }

        foreach ($report['rules'] ?? [] as $rule) {
            $checkRefs([$rule['evidence'] ?? ''], "Rule {$rule['id']}");
        }

        foreach ($report['verifiedOutcome'] ?? [] as $outcome) {
            $checkRefs([$outcome['evidence'] ?? ''], "Verified outcome {$outcome['claim']}");
        }

        $timeline = collect($report['timeline'] ?? []);
        if ($timeline->pluck('at')->values()->all() !== $timeline->sortBy('at')->pluck('at')->values()->all()) {
            $errors[] = 'Timeline order is not deterministic.';
        }

        $phaseTotal = collect($report['roadmap']['phases'] ?? [])->sum('price');
        if ($phaseTotal !== ($report['roadmap']['total'] ?? null)) {
            $errors[] = 'Roadmap phase prices do not total displayed amount.';
        }

        if ($errors !== []) {
            throw new RuntimeException(implode(' ', $errors));
        }

        return $errors;
    }

    /** @return array<string, mixed> */
    private function stage(string $id, string $label, string $expected, string $observed, string $status, array $evidence, string $rule, string $risk): array
    {
        return compact('id', 'label', 'expected', 'observed', 'status', 'evidence', 'rule', 'risk');
    }

    /** @return array<string, string> */
    private function rule(string $id, string $statement, string $purpose, string $evidence, string $enforcement, string $confidence, string $consequence, string $owner, string $test): array
    {
        return compact('id', 'statement', 'purpose', 'evidence', 'enforcement', 'confidence', 'consequence', 'owner', 'test');
    }

    /** @return array<string, mixed> */
    private function finding(string $id, string $title, string $severity, string $classification, string $workflow, array $evidence, string $consequence, string $response, string $urgency, string $acceptance): array
    {
        return compact('id', 'title', 'severity', 'classification', 'workflow', 'evidence', 'consequence', 'response', 'urgency', 'acceptance');
    }

    /** @return array<string, string> */
    private function containment(string $objective, string $purpose, string $precondition, string $action, string $owner, string $evidenceCaptured, string $risk, string $reversal, string $validation, string $limitation): array
    {
        return compact('objective', 'purpose', 'precondition', 'action', 'owner', 'evidenceCaptured', 'risk', 'reversal', 'validation', 'limitation');
    }

    /** @return array<int, array<string, string>> */
    private function timeline(LabRun $run, Incident $incident, Collection $attempts): array
    {
        $event = $run->events->first();
        $entitlement = $run->entitlements->first();
        $events = [
            ['id' => 'TL-01', 'at' => $run->payment->attempted_at->toIso8601String(), 'evidence' => 'EV-PAYMENT', 'source' => 'payment', 'correlation' => $run->correlation_id, 'stage' => 'payment', 'fact' => 'Synthetic payment succeeded.', 'state' => $run->payment->status, 'finding' => 'F-01'],
            ['id' => 'TL-02', 'at' => $event?->received_at?->toIso8601String() ?? $run->started_at->toIso8601String(), 'evidence' => 'EV-WEBHOOK', 'source' => 'webhook', 'correlation' => $event?->provider_event_id ?? $run->correlation_id, 'stage' => 'webhook', 'fact' => $run->webhook_deliveries.' signed deliveries retained for one event.', 'state' => 'accepted', 'finding' => 'F-02'],
        ];

        foreach ($attempts as $attempt) {
            /** @var ProvisioningAttempt $attempt */
            $events[] = ['id' => 'TL-Q'.$attempt->attempt_number, 'at' => $attempt->completed_at->toIso8601String(), 'evidence' => 'EV-QUEUE', 'source' => 'queue', 'correlation' => $attempt->job_uuid, 'stage' => 'queue', 'fact' => "Provisioning attempt {$attempt->attempt_number} {$attempt->status}.", 'state' => $attempt->failure_code ?? $attempt->status, 'finding' => 'F-02'];
        }

        $events[] = ['id' => 'TL-06', 'at' => $run->completed_at?->toIso8601String() ?? now()->toIso8601String(), 'evidence' => 'EV-ENTITLEMENT', 'source' => 'entitlement', 'correlation' => (string) $entitlement?->id, 'stage' => 'entitlement', 'fact' => 'Entitlement existed but remained locked.', 'state' => $entitlement?->state ?? 'missing', 'finding' => 'F-01'];
        $events[] = ['id' => 'TL-07', 'at' => $incident->detected_at->toIso8601String(), 'evidence' => 'EV-INCIDENT', 'source' => 'audit', 'correlation' => $run->correlation_id, 'stage' => 'access', 'fact' => 'Paid-but-locked SLA incident opened.', 'state' => $incident->status, 'finding' => 'F-01'];
        $events[] = ['id' => 'TL-08', 'at' => $incident->detected_at->copy()->addSecond()->toIso8601String(), 'evidence' => 'EV-RECOVERY', 'source' => 'approval', 'correlation' => $run->correlation_id, 'stage' => 'recovery', 'fact' => 'Approved action is bounded reconciliation of the existing entitlement.', 'state' => 'approved specimen decision', 'finding' => 'F-03'];
        $events[] = ['id' => 'TL-09', 'at' => $incident->detected_at->copy()->addSeconds(2)->toIso8601String(), 'evidence' => 'EV-RECOVERY', 'source' => 'reconciliation', 'correlation' => $run->correlation_id, 'stage' => 'recovery', 'fact' => 'Recovery activates one existing entitlement.', 'state' => 'repaired / 1 effect', 'finding' => 'F-03'];
        $events[] = ['id' => 'TL-10', 'at' => $incident->detected_at->copy()->addSeconds(3)->toIso8601String(), 'evidence' => 'EV-RECOVERY', 'source' => 'idempotency-check', 'correlation' => $run->correlation_id, 'stage' => 'recovery', 'fact' => 'Repeat recovery produces no_op and zero effects.', 'state' => 'no_op / 0 effects', 'finding' => 'F-03'];
        $events[] = ['id' => 'TL-11', 'at' => $incident->detected_at->copy()->addSeconds(4)->toIso8601String(), 'evidence' => 'EV-STABILISED', 'source' => 'verification', 'correlation' => $run->correlation_id, 'stage' => 'access', 'fact' => 'Verified outcome remains synthetic and scoped to the specimen workflow.', 'state' => 'verified in Lab', 'finding' => 'F-05'];

        return collect($events)->sortBy('at')->values()->all();
    }

    private function duration(?int $milliseconds): string
    {
        if ($milliseconds === null) {
            return 'pending';
        }

        return $milliseconds >= 60000 ? round($milliseconds / 60000).' minutes' : "{$milliseconds} ms";
    }

    private function approvedCtaUrl(): ?string
    {
        $url = trim((string) (config('rescue_lab.xray.cta_url') ?: config('rescue_lab.contact_url')));
        if ($url === '') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || str_contains($host, 'example.') || str_ends_with($host, '.test')) {
            return null;
        }

        return $url;
    }
}
