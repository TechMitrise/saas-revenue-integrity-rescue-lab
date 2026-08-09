<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#12231f">
    <title>{{ $report['metadata']['title'] }} | {{ $report['metadata']['subtitle'] }}</title>
    @vite(['resources/css/xray.css', 'resources/js/xray.js'])
</head>
<body class="xray-page">
    @php
        $money = fn ($value) => '$'.number_format((float) $value);
        $evidenceById = collect($report['evidence'])->keyBy('id');
        $findingsById = collect($report['findings'])->keyBy('id');
        $ctaUrl = $report['cta']['url'] ?: '#access-required';
        $queueStage = collect($report['stages'])->firstWhere('id', 'queue');
        $defaultStageId = $queueStage['id'] ?? $report['stages'][0]['id'];
        $printPageCount = 15;
    @endphp

    <header class="xray-top">
        <a class="xray-brand" href="{{ route('proof') }}"><span>TMPL</span><strong>Critical Software X-Ray<small>Revenue access failure specimen</small></strong></a>
        <nav aria-label="Specimen navigation">
            <a href="#guided">Guided</a>
            <a href="#full-report">Full report</a>
            <a href="#roadmap">Roadmap</a>
            <button type="button" data-print>Print / PDF</button>
        </nav>
        <form method="POST" action="{{ route('xray.lock') }}">@csrf<button class="lock-button" type="submit">Lock again</button></form>
    </header>

    <main>
        <section class="xray-hero xray-width" id="top">
            <div>
                <p class="xray-eyebrow">Synthetic professional-service specimen</p>
                <h1>Payment succeeded.<br><em>Access failed.</em></h1>
                <p>{{ $report['metadata']['belief'] }}</p>
                <div class="hero-actions">
                    <a class="xray-button primary" href="{{ $ctaUrl }}">{{ $report['cta']['label'] }}</a>
                    <a class="xray-button quiet" href="{{ route('proof') }}">Back to Rescue Lab proof</a>
                </div>
            </div>
            <aside class="brief-card">
                <span>Executive conclusion</span>
                <strong>{{ $report['executiveBrief']['conclusion'] }}</strong>
                <dl>
                    <div><dt>Severity</dt><dd>{{ $report['executiveBrief']['severity'] }}</dd></div>
                    <div><dt>Confidence</dt><dd>{{ $report['executiveBrief']['evidenceConfidence'] }}</dd></div>
                    <div><dt>X-Ray specimen</dt><dd>{{ $money($report['pricing']['xray']) }}</dd></div>
                </dl>
            </aside>
        </section>

        <section class="boundary xray-width" aria-label="Specimen boundary">
            <div><p class="xray-eyebrow">Boundary</p><h2>{{ $report['metadata']['specimenLabel'] }}</h2></div>
            <ul>
                @foreach ($report['boundary'] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </section>

        <section class="guided-shell xray-width" id="guided">
            <div class="section-head">
                <p class="xray-eyebrow">Guided three-minute view</p>
                <h2>One buyer path. Eight decisions.</h2>
                <p>Use Previous/Next, the step rail, or arrow keys to narrate the incident without technical wandering.</p>
            </div>
            <div class="guided-view" data-guided tabindex="0" aria-label="Guided Critical Software X-Ray path">
                <div class="guided-progress">
                    <span data-guided-count>1 / {{ count($report['guided']) }}</span>
                    <i aria-hidden="true"><b data-guided-meter></b></i>
                    <a href="#full-report">Open full report</a>
                </div>
                <ol class="guided-steps" aria-label="Guided path steps">
                    @foreach ($report['guided'] as $index => $step)
                        <li><button type="button" class="@if ($index === 0) active @endif" data-guided-jump="{{ $index }}" aria-controls="guided-step-{{ $index }}">{{ $index + 1 }}</button></li>
                    @endforeach
                </ol>
                @foreach ($report['guided'] as $index => $step)
                    <article class="guided-card @if ($index === 0) active @endif" id="guided-step-{{ $index }}" data-guided-step="{{ $index }}" aria-live="polite">
                        <small>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</small>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['summary'] }}</p>
                        <a href="#{{ $step['anchor'] }}">Inspect evidence</a>
                    </article>
                @endforeach
                <div class="guided-controls">
                    <button type="button" data-guided-prev>Previous</button>
                    <button type="button" data-guided-next>Next</button>
                </div>
            </div>
        </section>

        <section class="full-report xray-width" id="full-report">
            <div class="section-head">
                <p class="xray-eyebrow">Full X-Ray specimen</p>
                <h2>Evidence-backed decision document.</h2>
            </div>

            <section class="report-section" id="executive-brief">
                <div class="section-title"><span>A</span><h3>Executive Risk Brief</h3></div>
                <div class="executive-grid">
                    <article><span>Incident</span><strong>{{ $report['executiveBrief']['incidentTitle'] }}</strong></article>
                    <article><span>Workflow</span><strong>{{ $report['executiveBrief']['workflow'] }}</strong></article>
                    <article><span>Consequence</span><strong>{{ $report['executiveBrief']['confirmedConsequence'] }}</strong></article>
                    <article><span>Divergence</span><strong>{{ $report['executiveBrief']['divergence'] }}</strong></article>
                    <article><span>Decision</span><strong>{{ $report['executiveBrief']['decisionRequired'] }}</strong></article>
                    <article><span>Unknowns</span><strong>{{ $report['executiveBrief']['unknowns'] }}</strong></article>
                </div>
                <div class="impact-split">
                    <p><b>Confirmed impact:</b> {{ $report['executiveBrief']['confirmedImpact'] }}</p>
                    <p><b>Possible wider exposure:</b> {{ $report['executiveBrief']['possibleWiderExposure'] }}</p>
                </div>
            </section>

            <section class="report-section" id="critical-path">
                <div class="section-title"><span>B</span><h3>Critical-Path Map</h3></div>
                <div class="path-layout">
                    <div class="path-map" role="list">
                        @foreach ($report['stages'] as $stage)
                            <button type="button" class="path-stage {{ $stage['status'] }} @if ($stage['id'] === $defaultStageId) active @endif" data-stage="{{ $stage['id'] }}" role="listitem">
                                <b>{{ $loop->iteration }}</b><span>{{ $stage['label'] }}</span><small>{{ $stage['status'] }}</small>
                            </button>
                        @endforeach
                    </div>
                    <div class="stage-detail">
                        @foreach ($report['stages'] as $stage)
                            <article class="@if ($stage['id'] === $defaultStageId) active @endif" data-stage-panel="{{ $stage['id'] }}">
                                <p class="xray-eyebrow">{{ $stage['status'] === 'diverged' || $stage['status'] === 'blocked' ? 'First divergence evidence' : 'Stage evidence' }}</p>
                                <h4>{{ $stage['label'] }}</h4>
                                <dl>
                                    <div><dt>Expected</dt><dd>{{ $stage['expected'] }}</dd></div>
                                    <div><dt>Observed</dt><dd>{{ $stage['observed'] }}</dd></div>
                                    <div><dt>Rule</dt><dd>{{ $stage['rule'] }}</dd></div>
                                    <div><dt>Risk</dt><dd>{{ $stage['risk'] }}</dd></div>
                                </dl>
                                <div class="evidence-tags">
                                    @foreach ($stage['evidence'] as $id)
                                        <a href="#evidence-{{ $id }}">{{ $id }}</a>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="report-section" id="state-matrix">
                <div class="section-title"><span>C</span><h3>Expected vs Actual State</h3></div>
                <div class="matrix-table" role="table">
                    <div class="matrix-row heading" role="row"><span>Stage</span><span>Expected</span><span>Incident</span><span>Recovered</span><span>Evidence</span><span>Confidence</span></div>
                    @foreach ($report['stateMatrix'] as $row)
                        <div class="matrix-row" role="row">
                            <strong>{{ $row['stage'] }}</strong><span>{{ $row['expected'] }}</span><span>{{ $row['incident'] }}</span><span>{{ $row['recovered'] }}</span><a href="#evidence-{{ $row['evidence'] }}">{{ $row['evidence'] }}</a><span>{{ $row['confidence'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="state-compare">
                    <article><p class="xray-eyebrow">Before recovery</p><strong>Paid + active subscription + locked entitlement</strong><span>Customer value not delivered.</span></article>
                    <article><p class="xray-eyebrow">After recovery</p><strong>Existing entitlement active once</strong><span>Verified in this synthetic scenario.</span></article>
                    <article><p class="xray-eyebrow">Remaining risk</p><strong>Wider exposure unknown</strong><span>A real X-Ray checks adjacent workflows.</span></article>
                </div>
            </section>

            <section class="report-section" id="timeline">
                <div class="section-title"><span>D</span><h3>Evidence Timeline</h3></div>
                <ol class="xray-timeline">
                    @foreach ($report['timeline'] as $event)
                        <li data-stage-event="{{ $event['stage'] }}">
                            <time>{{ \Carbon\CarbonImmutable::parse($event['at'])->format('H:i:s') }}</time>
                            <div><p class="xray-eyebrow">{{ $event['source'] }} / {{ $event['stage'] }}</p><h4>{{ $event['fact'] }}</h4><code>{{ $event['correlation'] }}</code></div>
                            <a href="#evidence-{{ $event['evidence'] }}">{{ $event['evidence'] }}</a>
                            <a href="#finding-{{ $event['finding'] }}">{{ $event['finding'] }}</a>
                            <span>{{ $event['state'] }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="report-section" id="root-cause">
                <div class="section-title"><span>E</span><h3>Root-Cause Decision</h3></div>
                <div class="root-grid">
                    <article><span>Proximate failure</span><strong>{{ $report['rootCause']['proximateFailure'] }}</strong></article>
                    <article><span>Underlying control gap</span><strong>{{ $report['rootCause']['underlyingGap'] }}</strong></article>
                    <article><span>Why not earlier</span><strong>{{ $report['rootCause']['whyNotEarlier'] }}</strong></article>
                    <article><span>Confidence</span><strong>{{ $report['rootCause']['confidence'] }}</strong></article>
                </div>
                <h4>Competing explanations considered</h4>
                <div class="competing-grid">
                    @foreach ($report['rootCause']['competingExplanations'] as $item)
                        <article><b>{{ $item['explanation'] }}</b><p>{{ $item['decision'] }}</p><a href="#evidence-{{ $item['evidence'] }}">{{ $item['evidence'] }}</a></article>
                    @endforeach
                </div>
            </section>

            <section class="report-section" id="business-rules">
                <div class="section-title"><span>F</span><h3>Hidden Business-Rule Register</h3></div>
                <div class="rule-grid">
                    @foreach ($report['rules'] as $rule)
                        <article>
                            <p class="xray-eyebrow">{{ $rule['id'] }} / {{ $rule['confidence'] }}</p>
                            <h4>{{ $rule['statement'] }}</h4>
                            <dl><div><dt>Purpose</dt><dd>{{ $rule['purpose'] }}</dd></div><div><dt>Enforcement</dt><dd>{{ $rule['enforcement'] }}</dd></div><div><dt>Failure consequence</dt><dd>{{ $rule['consequence'] }}</dd></div><div><dt>Owner</dt><dd>{{ $rule['owner'] }}</dd></div><div><dt>Test/control</dt><dd>{{ $rule['test'] }}</dd></div></dl>
                            <a href="#evidence-{{ $rule['evidence'] }}">{{ $rule['evidence'] }}</a>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="report-section" id="findings">
                <div class="section-title"><span>G</span><h3>Prioritised Findings</h3></div>
                <div class="severity-filter" aria-label="Filter findings by severity">
                    <button class="active" type="button" data-severity-filter="all">All</button>
                    <button type="button" data-severity-filter="Critical">Critical</button>
                    <button type="button" data-severity-filter="High">High</button>
                    <button type="button" data-severity-filter="Medium">Medium</button>
                </div>
                <p class="severity-model"><b>Severity model:</b> {{ implode(' / ', $report['severityModel']) }}</p>
                <div class="finding-list">
                    @foreach ($report['findings'] as $finding)
                        <article id="finding-{{ $finding['id'] }}" data-severity="{{ $finding['severity'] }}">
                            <div><span class="severity {{ strtolower($finding['severity']) }}">{{ $finding['severity'] }}</span><small>{{ $finding['classification'] }}</small></div>
                            <h4>{{ $finding['id'] }}. {{ $finding['title'] }}</h4>
                            <p>{{ $finding['consequence'] }}</p>
                            <dl><div><dt>Workflow</dt><dd>{{ $finding['workflow'] }}</dd></div><div><dt>Response</dt><dd>{{ $finding['response'] }}</dd></div><div><dt>Urgency</dt><dd>{{ $finding['urgency'] }}</dd></div><div><dt>Acceptance</dt><dd>{{ $finding['acceptance'] }}</dd></div></dl>
                            <div class="evidence-tags">@foreach ($finding['evidence'] as $id)<a href="#evidence-{{ $id }}">{{ $id }}</a>@endforeach</div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="report-section" id="containment">
                <div class="section-title"><span>H</span><h3>Immediate Containment Plan</h3></div>
                <div class="containment-grid">
                    @foreach ($report['containment'] as $action)
                        <article>
                            <p class="xray-eyebrow">{{ $action['owner'] }}</p>
                            <h4>{{ $action['objective'] }}</h4>
                            <dl>
                                <div><dt>Precondition</dt><dd>{{ $action['precondition'] }}</dd></div>
                                <div><dt>Action</dt><dd>{{ $action['action'] }}</dd></div>
                                <div><dt>Evidence captured</dt><dd>{{ $action['evidenceCaptured'] }}</dd></div>
                                <div><dt>Risk</dt><dd>{{ $action['risk'] }}</dd></div>
                                <div><dt>Reversal</dt><dd>{{ $action['reversal'] }}</dd></div>
                                <div><dt>Validation</dt><dd>{{ $action['validation'] }}</dd></div>
                                <div><dt>Limitation</dt><dd>{{ $action['limitation'] }}</dd></div>
                            </dl>
                        </article>
                    @endforeach
                </div>
                <aside class="do-not"><h4>Do not do yet</h4><ul>@foreach ($report['doNotDoYet'] as $item)<li>{{ $item }}</li>@endforeach</ul></aside>
            </section>

            <section class="report-section" id="recovery">
                <div class="section-title"><span>I</span><h3>Recovery and Rollback Plan</h3></div>
                <div class="recovery-panel">
                    @foreach ($report['recovery'] as $key => $value)
                        <article>
                            <span>{{ str($key)->headline() }}</span>
                            @if (is_array($value))
                                <ul>@foreach ($value as $item)<li>{{ $item }}</li>@endforeach</ul>
                            @else
                                <strong>{{ $value }}</strong>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="report-section" id="verified-outcome">
                <div class="section-title"><span>J</span><h3>Verified Outcome</h3></div>
                <div class="outcome-grid">
                    @foreach ($report['verifiedOutcome'] as $outcome)
                        <article><span>{{ $outcome['status'] }}</span><strong>{{ $outcome['claim'] }}</strong><a href="#evidence-{{ $outcome['evidence'] }}">{{ $outcome['evidence'] }}</a></article>
                    @endforeach
                </div>
            </section>

            <section class="report-section" id="roadmap">
                <div class="section-title"><span>K</span><h3>Priced 30-Day Rescue Roadmap</h3></div>
                <div class="pricing-callout">
                    <div><span>Illustrative Critical Software X-Ray</span><strong>{{ $money($report['pricing']['xray']) }}</strong></div>
                    <div><span>Illustrative 30-day Sprint</span><strong>{{ $money($report['pricing']['sprint']) }}</strong></div>
                    <p>{{ $report['pricing']['creditWindow'] }} {{ $report['pricing']['label'] }}</p>
                </div>
                <div class="roadmap-grid">
                    @foreach ($report['roadmap']['phases'] as $phase)
                        <article>
                            <p class="xray-eyebrow">{{ $phase['window'] }}</p>
                            <h4>{{ $phase['phase'] }}</h4>
                            <strong>{{ $money($phase['price']) }}</strong>
                            <p>{{ $phase['outcome'] }}</p>
                            <dl><div><dt>Deliverables</dt><dd>{{ implode('; ', $phase['deliverables']) }}</dd></div><div><dt>Acceptance</dt><dd>{{ $phase['acceptance'] }}</dd></div><div><dt>Buyer responsibility</dt><dd>{{ $phase['buyerResponsibility'] }}</dd></div></dl>
                        </article>
                    @endforeach
                </div>
                <div class="roadmap-boundary">
                    <article><h4>Assumptions</h4><ul>@foreach ($report['roadmap']['assumptions'] as $item)<li>{{ $item }}</li>@endforeach</ul></article>
                    <article><h4>Exclusions</h4><ul>@foreach ($report['roadmap']['exclusions'] as $item)<li>{{ $item }}</li>@endforeach</ul></article>
                    <article><h4>Scope-change conditions</h4><ul>@foreach ($report['roadmap']['scopeChangeConditions'] as $item)<li>{{ $item }}</li>@endforeach</ul></article>
                </div>
            </section>

            <section class="report-section" id="deliverables">
                <div class="section-title"><span>L</span><h3>Real X-Ray Deliverables</h3></div>
                <div class="deliverable-list">@foreach ($report['deliverables'] as $item)<span>{{ $item }}</span>@endforeach</div>
                <p class="deliverable-note">The X-Ray is not “a PDF.” It is an evidence-backed decision that reduces the risk of unsafe development, rewriting or automation.</p>
            </section>

            <section class="report-section" id="evidence">
                <div class="section-title"><span>M</span><h3>Evidence Register</h3></div>
                <div class="evidence-list">
                    @foreach ($report['evidence'] as $item)
                        <article id="evidence-{{ $item['id'] }}"><p class="xray-eyebrow">{{ $item['id'] }} / {{ $item['type'] }}</p><h4>{{ $item['label'] }}</h4><code>{{ $item['source'] }}</code><p>{{ $item['value'] }}</p><span>{{ $item['confidence'] }}</span></article>
                    @endforeach
                </div>
            </section>

            <section class="cta-panel" id="access-required">
                <div><p class="xray-eyebrow">Responsible first purchase</p><h3>{{ $report['cta']['label'] }}</h3><p>{{ $report['cta']['supporting'] }}</p></div>
                <a class="xray-button primary" href="{{ $ctaUrl }}">{{ $report['cta']['label'] }}</a>
            </section>
        </section>
    </main>

    <section class="xray-print-document" aria-label="Printable Critical Software X-Ray specimen">
        <article class="print-page print-cover">
            <header><span>Tech Mitrise</span><b>Page 01 / {{ $printPageCount }}</b></header>
            <div class="print-cover-grid">
                <div>
                    <p class="xray-eyebrow">Critical Software X-Ray specimen</p>
                    <h2>Payment succeeded.<br><em>Access failed.</em></h2>
                    <p>{{ $report['metadata']['publicPromise'] }}</p>
                </div>
                <aside>
                    <strong>{{ $report['metadata']['specimenLabel'] }}</strong>
                    <ul>@foreach ($report['boundary'] as $item)<li>{{ $item }}</li>@endforeach</ul>
                    <dl><div><dt>X-Ray</dt><dd>{{ $money($report['pricing']['xray']) }}</dd></div><div><dt>Roadmap</dt><dd>{{ $money($report['pricing']['sprint']) }}</dd></div></dl>
                </aside>
            </div>
            <footer>Revenue Access Failure / Synthetic buyer specimen</footer>
        </article>

        <article class="print-page">
            <header><span>Executive Risk Brief</span><b>Page 02 / {{ $printPageCount }}</b></header>
            <h2>What failed, why it mattered, and what decision is required.</h2>
            <div class="print-grid three">
                <section><small>Incident</small><strong>{{ $report['executiveBrief']['incidentTitle'] }}</strong></section>
                <section><small>Workflow</small><strong>{{ $report['executiveBrief']['workflow'] }}</strong></section>
                <section><small>Consequence</small><strong>{{ $report['executiveBrief']['confirmedConsequence'] }}</strong></section>
                <section><small>First divergence</small><strong>{{ $report['executiveBrief']['divergence'] }}</strong></section>
                <section><small>Decision required</small><strong>{{ $report['executiveBrief']['decisionRequired'] }}</strong></section>
                <section><small>Confidence</small><strong>{{ $report['executiveBrief']['evidenceConfidence'] }}</strong></section>
            </div>
            <div class="print-callout"><b>Confirmed impact:</b> {{ $report['executiveBrief']['confirmedImpact'] }}</div>
            <div class="print-grid three">
                <section><small>Buyer should understand</small><strong>The cash path was healthy; customer value delivery was not.</strong></section>
                <section><small>Buyer should not infer</small><strong>This specimen does not prove real-client revenue loss or wider production exposure.</strong></section>
                <section><small>Next decision</small><strong>Approve a scoped X-Ray before rewrite, broad retry or automation.</strong></section>
            </div>
            <footer>{{ $report['metadata']['specimenLabel'] }}</footer>
        </article>

        <article class="print-page">
            <header><span>Critical-Path Map</span><b>Page 03 / {{ $printPageCount }}</b></header>
            <h2>First proven divergence: {{ $queueStage['label'] ?? 'Queue / job' }}.</h2>
            <div class="print-path">
                @foreach ($report['stages'] as $stage)
                    <section class="{{ $stage['id'] === $defaultStageId ? 'selected' : '' }}"><b>{{ $loop->iteration }}</b><strong>{{ $stage['label'] }}</strong><small>{{ $stage['status'] }}</small></section>
                @endforeach
            </div>
            @if ($queueStage)
                <div class="print-grid two">
                    <section><small>Expected</small><strong>{{ $queueStage['expected'] }}</strong></section>
                    <section><small>Observed</small><strong>{{ $queueStage['observed'] }}</strong></section>
                    <section><small>Rule</small><strong>{{ $queueStage['rule'] }}</strong></section>
                    <section><small>Risk</small><strong>{{ $queueStage['risk'] }}</strong></section>
                </div>
                <p class="print-evidence">{{ implode(' / ', $queueStage['evidence']) }}</p>
            @endif
            <div class="print-grid three">
                <section><small>What failed</small><strong>Async fulfilment exhausted bounded attempts.</strong></section>
                <section><small>What held</small><strong>Payment and duplicate-delivery controls preserved one business effect.</strong></section>
                <section><small>What to avoid</small><strong>Do not blindly replay events or create replacement entitlements.</strong></section>
            </div>
            <footer>Expected state is compared with observed state before recommendation.</footer>
        </article>

        <article class="print-page">
            <header><span>Expected vs Actual vs Recovered</span><b>Page 04 / {{ $printPageCount }}</b></header>
            <h2>The issue is access-state divergence, not payment failure.</h2>
            <table class="print-table">
                <thead><tr><th>Stage</th><th>Expected</th><th>Incident</th><th>Recovered</th><th>Evidence</th></tr></thead>
                <tbody>@foreach ($report['stateMatrix'] as $row)<tr><th>{{ $row['stage'] }}</th><td>{{ $row['expected'] }}</td><td>{{ $row['incident'] }}</td><td>{{ $row['recovered'] }}</td><td>{{ $row['evidence'] }}</td></tr>@endforeach</tbody>
            </table>
            <div class="print-grid three">
                <section><small>Before</small><strong>Paid + active subscription + locked entitlement</strong></section>
                <section><small>After</small><strong>Existing entitlement active once</strong></section>
                <section><small>Remaining risk</small><strong>Wider exposure unknown outside the fixture</strong></section>
            </div>
            <div class="print-grid three">
                <section><small>Rejected explanation</small><strong>Payment-provider failure is not supported by this evidence.</strong></section>
                <section><small>Confirmed repair shape</small><strong>Update the existing entitlement, then verify repeat no-op.</strong></section>
                <section><small>Open question</small><strong>A real X-Ray checks adjacent workflows and ownership boundaries.</strong></section>
            </div>
            <footer>Facts, recommendations and unknowns remain separated.</footer>
        </article>

        <article class="print-page">
            <header><span>Evidence Timeline</span><b>Page 05 / {{ $printPageCount }}</b></header>
            <h2>Every material claim is tied to persisted evidence.</h2>
            <ol class="print-timeline">@foreach ($report['timeline'] as $event)<li><time>{{ \Carbon\CarbonImmutable::parse($event['at'])->format('H:i:s') }}</time><div><strong>{{ $event['fact'] }}</strong><span>{{ $event['source'] }} / {{ $event['state'] }}</span></div><b>{{ $event['evidence'] }}</b></li>@endforeach</ol>
            <div class="print-grid three">
                <section><small>Observed</small><strong>Payment, webhook, queue, entitlement and incident records.</strong></section>
                <section><small>Verified</small><strong>Recovery and repeat no-op are backed by command/test design.</strong></section>
                <section><small>Unknown</small><strong>Client-specific wider exposure is not inferred from the lab.</strong></section>
            </div>
            <footer>Timeline order is deterministic and validated by tests.</footer>
        </article>

        <article class="print-page">
            <header><span>Root-Cause Decision</span><b>Page 06 / {{ $printPageCount }}</b></header>
            <h2>{{ $report['rootCause']['proximateFailure'] }}</h2>
            <div class="print-grid two">
                <section><small>Underlying gap</small><strong>{{ $report['rootCause']['underlyingGap'] }}</strong></section>
                <section><small>Why not earlier</small><strong>{{ $report['rootCause']['whyNotEarlier'] }}</strong></section>
                <section><small>Confidence</small><strong>{{ $report['rootCause']['confidence'] }}</strong></section>
                <section><small>Unknowns</small><strong>{{ $report['rootCause']['unknowns'] }}</strong></section>
            </div>
            <h3>Competing explanations considered</h3>
            <div class="print-grid three">@foreach ($report['rootCause']['competingExplanations'] as $item)<section><small>{{ $item['evidence'] }}</small><strong>{{ $item['explanation'] }}</strong><p>{{ $item['decision'] }}</p></section>@endforeach</div>
            <footer>Rejected explanations are documented instead of hidden.</footer>
        </article>

        <article class="print-page">
            <header><span>Hidden Business Rule Atlas Sample</span><b>Page 07 / {{ $printPageCount }}</b></header>
            <h2>Rules that must survive recovery, rewrite and automation.</h2>
            <table class="print-table compact">
                <thead><tr><th>Rule</th><th>Statement</th><th>Control</th><th>Evidence</th></tr></thead>
                <tbody>@foreach ($report['rules'] as $rule)<tr><th>{{ $rule['id'] }}</th><td>{{ $rule['statement'] }}</td><td>{{ $rule['test'] }}</td><td>{{ $rule['evidence'] }}</td></tr>@endforeach</tbody>
            </table>
            <table class="print-table compact">
                <thead><tr><th>Rule</th><th>Owner</th><th>Failure consequence</th><th>Confidence</th></tr></thead>
                <tbody>@foreach ($report['rules'] as $rule)<tr><th>{{ $rule['id'] }}</th><td>{{ $rule['owner'] }}</td><td>{{ $rule['consequence'] }}</td><td>{{ $rule['confidence'] }}</td></tr>@endforeach</tbody>
            </table>
            <footer>Business rules are made explicit before broad implementation.</footer>
        </article>

        <article class="print-page">
            <header><span>Prioritised Findings</span><b>Page 08 / {{ $printPageCount }}</b></header>
            <h2>What must be fixed first.</h2>
            <div class="print-findings">@foreach ($report['findings'] as $finding)<section><small>{{ $finding['severity'] }} / {{ $finding['classification'] }}</small><strong>{{ $finding['id'] }}. {{ $finding['title'] }}</strong><p>{{ $finding['consequence'] }}</p><p><b>Response:</b> {{ $finding['response'] }}</p><p><b>Urgency:</b> {{ $finding['urgency'] }}</p><p><b>Acceptance:</b> {{ $finding['acceptance'] }}</p><b>{{ implode(' / ', $finding['evidence']) }}</b></section>@endforeach</div>
            <footer>Severity combines impact, likelihood, detectability, recoverability and evidence confidence.</footer>
        </article>

        <article class="print-page">
            <header><span>Immediate Containment</span><b>Page 09 / {{ $printPageCount }}</b></header>
            <h2>Contain without creating duplicate effects.</h2>
            <div class="print-grid two">@foreach ($report['containment'] as $action)<section><small>{{ $action['owner'] }}</small><strong>{{ $action['objective'] }}</strong><p>{{ $action['action'] }}</p><b>Validation: {{ $action['validation'] }}</b></section>@endforeach</div>
            <aside class="print-warning"><strong>Do not do yet:</strong> {{ implode(' ', $report['doNotDoYet']) }}</aside>
            <footer>Containment is scoped before recovery is run.</footer>
        </article>

        <article class="print-page">
            <header><span>Recovery and Rollback Plan</span><b>Page 10 / {{ $printPageCount }}</b></header>
            <h2>{{ $report['recovery']['approvedAction'] }}</h2>
            <div class="print-grid three">
                <section><small>Prerequisites</small><strong>{{ $report['recovery']['prerequisites'] }}</strong></section>
                <section><small>Boundary</small><strong>{{ $report['recovery']['boundary'] }}</strong></section>
                <section><small>Idempotency</small><strong>{{ $report['recovery']['idempotency'] }}</strong></section>
                <section><small>Rollback trigger</small><strong>{{ $report['recovery']['rollbackTrigger'] }}</strong></section>
                <section><small>Rollback action</small><strong>{{ $report['recovery']['rollbackAction'] }}</strong></section>
                <section><small>Owner</small><strong>{{ $report['recovery']['owner'] }}</strong></section>
            </div>
            <div class="print-grid two">
                <section><small>Verification</small><ul>@foreach ($report['recovery']['verification'] as $item)<li>{{ $item }}</li>@endforeach</ul></section>
                <section><small>Monitoring</small><strong>{{ $report['recovery']['monitoring'] }}</strong></section>
            </div>
            <footer>Rollback avoids rewriting authoritative payment evidence.</footer>
        </article>

        <article class="print-page">
            <header><span>Verified Synthetic Outcome and Remaining Risk</span><b>Page 11 / {{ $printPageCount }}</b></header>
            <h2>Access is restored once; wider exposure is still not invented.</h2>
            <div class="print-grid two">@foreach ($report['verifiedOutcome'] as $outcome)<section><small>{{ $outcome['status'] }}</small><strong>{{ $outcome['claim'] }}</strong><b>{{ $outcome['evidence'] }}</b></section>@endforeach</div>
            <div class="print-callout"><b>Remaining risk:</b> {{ $report['executiveBrief']['possibleWiderExposure'] }} {{ $report['executiveBrief']['unknowns'] }}</div>
            <footer>Verified outcome is synthetic and scoped to the selected workflow.</footer>
        </article>

        <article class="print-page">
            <header><span>Priced 30-Day Roadmap</span><b>Page 12 / {{ $printPageCount }}</b></header>
            <h2>{{ $money($report['pricing']['xray']) }} X-Ray first. {{ $money($report['roadmap']['total']) }} specimen sprint if justified.</h2>
            <div class="print-roadmap">@foreach ($report['roadmap']['phases'] as $phase)<section><small>{{ $phase['window'] }}</small><strong>{{ $phase['phase'] }}</strong><b>{{ $money($phase['price']) }}</b><p>{{ $phase['outcome'] }}</p><span><i>Deliverables:</i> {{ implode('; ', $phase['deliverables']) }}</span><span><i>Acceptance:</i> {{ $phase['acceptance'] }}</span><span><i>Buyer:</i> {{ $phase['buyerResponsibility'] }}</span></section>@endforeach</div>
            <div class="print-callout">{{ $report['pricing']['creditWindow'] }} {{ $report['pricing']['label'] }}</div>
            <footer>Roadmap total is validated against phase prices.</footer>
        </article>

        <article class="print-page">
            <header><span>Assumptions, Exclusions and Buyer Responsibilities</span><b>Page 13 / {{ $printPageCount }}</b></header>
            <h2>What is included, excluded and required.</h2>
            <div class="print-grid three">
                <section><small>Assumptions</small><ul>@foreach ($report['roadmap']['assumptions'] as $item)<li>{{ $item }}</li>@endforeach</ul></section>
                <section><small>Exclusions</small><ul>@foreach ($report['roadmap']['exclusions'] as $item)<li>{{ $item }}</li>@endforeach</ul></section>
                <section><small>Access required</small><ul>@foreach ($report['roadmap']['accessRequired'] as $item)<li>{{ $item }}</li>@endforeach</ul></section>
            </div>
            <div class="print-grid two">
                <section><small>Scope-change conditions</small><ul>@foreach ($report['roadmap']['scopeChangeConditions'] as $item)<li>{{ $item }}</li>@endforeach</ul></section>
                <section><small>Next decision</small><strong>{{ $report['roadmap']['nextDecision'] }}</strong></section>
            </div>
            <div class="print-callout"><b>Change control:</b> {{ $report['roadmap']['changeControl'] }} {{ $report['roadmap']['deploymentResponsibility'] }}</div>
            <footer>Production deployment authority remains with the buyer unless separately agreed.</footer>
        </article>

        <article class="print-page">
            <header><span>What a Real Critical Software X-Ray Delivers</span><b>Page 14 / {{ $printPageCount }}</b></header>
            <h2>An evidence-backed decision package, not a generic PDF.</h2>
            <div class="print-deliverables">@foreach ($report['deliverables'] as $item)<span>{{ $item }}</span>@endforeach</div>
            <h3>Evidence register</h3>
            <div class="print-evidence-grid">@foreach ($report['evidence'] as $item)<section><small>{{ $item['id'] }} / {{ $item['type'] }}</small><strong>{{ $item['label'] }}</strong><span>{{ $item['confidence'] }}</span></section>@endforeach</div>
            <footer>Reusable proprietary assessment internals are intentionally redacted.</footer>
        </article>

        <article class="print-page print-final">
            <header><span>Final CTA</span><b>Page 15 / {{ $printPageCount }}</b></header>
            <h2>{{ $report['cta']['label'] }}</h2>
            <p>{{ $report['cta']['supporting'] }}</p>
            <div class="print-cta">
                <strong>{{ $report['cta']['label'] }}</strong>
                @if ($ctaUrl !== '#access-required')
                    <a href="{{ $ctaUrl }}">{{ $ctaUrl }}</a>
                @else
                    <span>CTA destination remains configurable through TRIAGE_CTA_URL.</span>
                @endif
            </div>
            <aside>
                <b>Bring one critical workflow:</b>
                <span>failing, fragile or too risky to change.</span>
            </aside>
            <footer>{{ $report['metadata']['specimenLabel'] }}</footer>
        </article>
    </section>
</body>
</html>
