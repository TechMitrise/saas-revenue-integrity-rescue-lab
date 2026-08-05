<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10251f">
    <meta name="robots" content="index,follow">
    <meta name="description" content="Executable Laravel proof of a paid-but-locked SaaS incident, duplicate webhook handling, queue failure and safe reconciliation.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="SaaS Revenue Integrity Rescue Lab">
    <meta property="og:description" content="Payment succeeded. Access failed. Inspect the evidence, repair once and prove the repeat is harmless.">
    <meta property="og:image" content="{{ asset('images/rescue-lab-social.png') }}">
    <meta property="og:url" content="{{ route('proof') }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ route('proof') }}">
    <title>SaaS Revenue Integrity Rescue Lab | Engineering Proof</title>
    @vite('resources/css/proof.css')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareSourceCode',
        'name' => 'SaaS Revenue Integrity Rescue Lab',
        'description' => 'Synthetic Laravel lab demonstrating paid-but-locked detection and safe entitlement reconciliation.',
        'programmingLanguage' => ['PHP', 'JavaScript'],
        'runtimePlatform' => 'Laravel 13, PHP 8.5, MySQL 8.4, Valkey 8',
        'codeRepository' => config('rescue_lab.repository_url'),
    ], JSON_UNESCAPED_SLASHES) !!}</script>
</head>
<body>
    @php($liveDemoAvailable = config('rescue_lab.demo_mutations_enabled'))
    <div class="proof-grain" aria-hidden="true"></div>
    <header class="proof-header proof-width">
        <a class="proof-brand" href="{{ route('proof') }}" aria-label="Revenue Integrity Rescue Lab home">
            <span>RI</span>
            <strong>Rescue Lab<small>Executable engineering proof</small></strong>
        </a>
        <nav aria-label="Proof page navigation">
            <a href="#incident">Incident</a>
            <a href="#evidence">Evidence</a>
            <a href="#controls">Controls</a>
            @if (config('rescue_lab.repository_url'))
                <a href="{{ config('rescue_lab.repository_url') }}" rel="noopener">GitHub</a>
            @endif
        </nav>
        <a class="header-cta" href="{{ $liveDemoAvailable ? route('dashboard') : '#evidence' }}">{{ $liveDemoAvailable ? 'Open live lab' : 'Inspect proof' }} <span aria-hidden="true">{!! $liveDemoAvailable ? '&#8599;' : '&#8595;' !!}</span></a>
    </header>

    <main>
        <section class="proof-hero proof-width">
            <div class="hero-message">
                <p class="proof-eyebrow">Laravel rescue proof / Revenue path 001</p>
                <h1>Revenue leaks rarely start at checkout.<br><em>They start after payment.</em></h1>
                <p class="hero-lede">A successful payment can still become churn when duplicate events, failed queue jobs and slow provisioning leave a customer locked out. This lab makes that invisible failure observable, recoverable and safe to repeat.</p>
                <div class="hero-actions">
                    @if ($liveDemoAvailable)
                        <a class="proof-button primary" href="{{ route('dashboard') }}">Run the 8-action rescue <span>&#8599;</span></a>
                    @else
                        <a class="proof-button primary" href="#evidence">Review executable evidence <span>&#8595;</span></a>
                    @endif
                    <a class="proof-button text" href="#evidence">Inspect the proof <span>&#8595;</span></a>
                </div>
                <p class="hero-boundary"><i></i> Synthetic organisations, payments, failures, timings and revenue only.</p>
            </div>

            <aside class="incident-artifact" aria-label="Synthetic paid-but-locked incident summary">
                <div class="artifact-top"><span>Incident RI-PAID-001</span><b>Open</b></div>
                <div class="artifact-customer"><small>Synthetic enterprise</small><strong>Northstar Labs</strong><code>corr_ paid &#8594; locked</code></div>
                <dl>
                    <div><dt>Payment</dt><dd>Succeeded</dd></div>
                    <div class="danger"><dt>Access</dt><dd>Locked</dd></div>
                    <div><dt>Monthly exposure</dt><dd>$4,800</dd></div>
                    <div><dt>Entitlement SLA</dt><dd>Breached</dd></div>
                </dl>
                <div class="artifact-path">
                    <span><b>10</b> deliveries</span><i>&#8594;</i><span><b>1</b> event</span><i>&#8594;</i><span><b>1</b> effect</span>
                </div>
                <div class="artifact-action"><span>Recommended action</span><strong>Reconcile the existing entitlement under transaction and row locks.</strong></div>
            </aside>
        </section>

        <section class="proof-strip" aria-label="Release proof summary">
            <div class="proof-width proof-ledger">
                <div><strong>10 &#8594; 1 &#8594; 1</strong><span>Deliveries / event / effect</span></div>
                <div><strong>3 / 3</strong><span>Persisted queue failures</span></div>
                <div><strong>1 + 0</strong><span>Repair / repeat effects</span></div>
                <div><strong>122</strong><span>PHP assertions passing</span></div>
                <div><strong>4</strong><span>Desktop + mobile journeys</span></div>
            </div>
        </section>

        <section class="proof-section proof-width" id="incident">
            <div class="section-intro">
                <p class="proof-eyebrow">The live story</p>
                <h2>Do not tell prospects it works.<br><em>Let them break and rescue it.</em></h2>
                <p>Each guided action invokes the same application services covered by the automated tests, then reloads the observed MySQL state.</p>
            </div>
            <ol class="rescue-sequence">
                <li><span>01</span><div><small>Cash is healthy</small><h3>Record successful payment</h3><p>Create the synthetic paid subscription and one correlation trail.</p></div><code>USD 4,800 / succeeded</code></li>
                <li><span>02</span><div><small>Delivery is noisy</small><h3>Deliver one webhook 10 times</h3><p>Retain every signed delivery while consuming one provider event once.</p></div><code>10 / 1 / 1</code></li>
                <li><span>03</span><div><small>Provisioning breaks</small><h3>Exhaust bounded retries</h3><p>Persist three controlled timeouts without creating another entitlement.</p></div><code>3 attempts / locked</code></li>
                <li><span>04</span><div><small>Business impact appears</small><h3>Detect the SLA breach</h3><p>Translate technical state into one incident and measurable synthetic exposure.</p></div><code>open / USD 4,800</code></li>
                <li><span>05</span><div><small>Recovery is constrained</small><h3>Repair once, repeat safely</h3><p>Activate the existing entitlement, then prove the second run produces no effect.</p></div><code>repaired: 1 / no_op: 0</code></li>
            </ol>
        </section>

        <section class="evidence-section" id="evidence">
            <div class="proof-width">
                <div class="section-intro light">
                    <p class="proof-eyebrow">Claim-to-code ledger</p>
                    <h2>Every claim resolves to<br><em>stored or executable evidence.</em></h2>
                </div>
                <div class="evidence-table" role="table" aria-label="Claim to evidence mapping">
                    <div class="evidence-row heading" role="row"><span>Claim</span><span>Observed proof</span><span>Executable source</span></div>
                    <div class="evidence-row" role="row"><strong>Duplicate-safe ingestion</strong><span>10 deliveries, 1 provider event, 1 business effect</span><code>WebhookIngestor.php</code></div>
                    <div class="evidence-row" role="row"><strong>Bounded queue failure</strong><span>3 persisted attempts with deterministic timeout evidence</span><code>ProvisionEntitlement.php</code></div>
                    <div class="evidence-row" role="row"><strong>Revenue-aware detection</strong><span>Paid + locked + SLA breach = USD 4,800 incident</span><code>RevenueIntegrityAuditor.php</code></div>
                    <div class="evidence-row" role="row"><strong>Safe reconciliation</strong><span>Locked &#8594; active once; repeat = no_op / 0 effects</span><code>ReconciliationService.php</code></div>
                    <div class="evidence-row" role="row"><strong>Release confidence</strong><span>PHPUnit, Playwright, build and security audits</span><code>make test</code></div>
                </div>
            </div>
        </section>

        <section class="proof-section proof-width" id="controls">
            <div class="section-intro split-intro">
                <div><p class="proof-eyebrow">Legacy modernisation proof</p><h2>Same fixture.<br><em>Different controls.</em></h2></div>
                <p>The pitch is not &ldquo;we build CRUD.&rdquo; The proof is that we can isolate a revenue-critical failure, preserve evidence, introduce controls at the correct boundaries and demonstrate changed behaviour.</p>
            </div>
            <div class="before-after">
                <article class="path-card fragile">
                    <div><p class="proof-eyebrow">Fragile path</p><span>Revenue path breaks</span></div>
                    <h3>Payment is accepted.<br>Access remains locked.</h3>
                    <ul><li>Duplicate provider deliveries</li><li>Failed entitlement retries</li><li>Breached access SLA</li><li>Manual recovery risk</li></ul>
                    <strong>Outcome: paid-but-locked incident</strong>
                </article>
                <article class="path-card stable">
                    <div><p class="proof-eyebrow">Stabilised path</p><span>Business effect holds</span></div>
                    <h3>At-least-once delivery.<br>Effectively-once outcome.</h3>
                    <ul><li>Signature verification + inbox</li><li>Unique provider event + entitlement</li><li>Bounded retries with visibility</li><li>Transactional reconciliation</li></ul>
                    <strong>Outcome: one active entitlement within SLA</strong>
                </article>
            </div>
        </section>

        <section class="launch-cta proof-width">
            <div><p class="proof-eyebrow">Proof before proposal</p><h2>Bring the legacy revenue path.<br><em>We will make the risk observable.</em></h2></div>
            <div><p>Run the synthetic incident live, inspect its evidence, and use the architecture and operations package to evaluate the engineering approach.</p><a class="proof-button primary" href="{{ $liveDemoAvailable ? route('dashboard') : '#evidence' }}">{{ $liveDemoAvailable ? 'Open the Rescue Lab' : 'Review the evidence ledger' }} <span aria-hidden="true">{!! $liveDemoAvailable ? '&#8599;' : '&#8593;' !!}</span></a></div>
        </section>
    </main>

    <footer class="proof-footer">
        <div class="proof-width"><span>Laravel 13 / PHP 8.5 / MySQL 8.4 / Valkey 8</span><span>At-least-once delivery / Effectively-once business effect</span></div>
    </footer>
</body>
</html>
