<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#12231f">
    <meta name="robots" content="noindex,nofollow">
    <title>Rescue Control Room</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="control-room-page">
    <div class="ambient ambient-coral"></div>
    <div class="ambient ambient-gold"></div>
    <div id="dashboard-app" class="app-shell">
        <header class="topbar page-width">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">RI</span>
                <span><strong>Rescue Lab</strong><small>SaaS revenue integrity</small></span>
            </a>
            <div class="top-actions">
                <span class="synthetic-chip">Synthetic lab evidence</span>
                <button class="quiet-button" id="reset-button" type="button" @if (! config('rescue_lab.demo_mutations_enabled')) hidden @endif>Replay scenario</button>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="quiet-button" type="submit">Sign out</button></form>
            </div>
        </header>

        <main class="page-width">
            <section class="hero">
                <div>
                    <p class="eyebrow">Revenue path / Incident RI-PAID-001</p>
                    <h1>Payment succeeded.<br><em>Access failed.</em></h1>
                    <p class="hero-copy">The system traced, recovered and stabilised the revenue path safely.</p>
                </div>
                <aside class="guarantee-card">
                    <span>Accurate processing guarantee</span>
                    <strong id="guarantee">Loading evidence...</strong>
                    <small>No claim of distributed exactly-once delivery.</small>
                </aside>
            </section>

            <section class="guided-demo" id="guided-demo">
                <div class="guided-heading">
                    <div>
                        <p class="eyebrow">Guided live incident / 8 observable actions</p>
                        <h2>Watch the failure happen.<br><em>Then rescue it safely.</em></h2>
                        <p>One click performs one real application action. The evidence tabs below update from MySQL after every step.</p>
                    </div>
                    <span class="guide-progress" id="guide-progress">0 / 8 actions completed</span>
                </div>
                <div class="guide-layout">
                    <ol class="guide-steps" id="guide-steps" aria-label="Guided incident steps"></ol>
                    <aside class="guide-console">
                        <div class="guide-console-head">
                            <span>Live state</span>
                            <i id="guide-status">Ready</i>
                        </div>
                        <h3 id="guide-summary">Start from a successful payment.</h3>
                        <p id="guide-explanation">We will build the incident one persisted action at a time, then prove the repair is safe to repeat.</p>
                        <div class="guide-facts" id="guide-facts"></div>
                        <div class="guide-actions">
                            <button class="primary-button guide-action" id="guide-action" type="button">Start: record successful payment</button>
                            <button class="guide-restart" id="guide-restart" type="button">Restart guided incident</button>
                        </div>
                    </aside>
                </div>
            </section>

            <nav class="journey-nav" aria-label="Rescue evidence journey">
                <button class="journey-step active" data-screen="control" type="button"><b>01</b><span>Control Room</span></button>
                <button class="journey-step" data-screen="incident" type="button"><b>02</b><span>Customer Incident</span></button>
                <button class="journey-step" data-screen="root" type="button"><b>03</b><span>Root Cause</span></button>
                <button class="journey-step" data-screen="recovery" type="button"><b>04</b><span>Safe Recovery</span></button>
                <button class="journey-step" data-screen="comparison" type="button"><b>05</b><span>Fragile vs Stabilised</span></button>
                <button class="journey-step" data-screen="engineering" type="button"><b>06</b><span>Engineering Evidence</span></button>
            </nav>

            <div id="screen-status" class="screen-status" role="status" aria-live="polite"></div>

            <section class="screen-panel active" data-panel="control">
                <div class="section-heading"><div><p class="eyebrow">01 / Rescue Control Room</p><h2>The revenue path is broken after payment.</h2></div><span class="incident-state" id="incident-state">Loading</span></div>
                <div class="metric-grid">
                    <article><span>Customer affected</span><strong id="metric-customer">...</strong><small>Synthetic organisation</small></article>
                    <article class="metric-danger"><span>Monthly revenue exposed</span><strong id="metric-revenue">...</strong><small>Paid subscription amount</small></article>
                    <article><span>Payment to access</span><strong id="metric-delay">...</strong><small id="metric-sla">Entitlement SLA</small></article>
                    <article><span>Access state</span><strong id="metric-access">...</strong><small>Persisted entitlement</small></article>
                </div>
                <div class="incident-brief">
                    <div><span class="signal-dot"></span><p class="eyebrow">Active incident</p><h3 id="incident-title">Loading incident...</h3><p id="incident-reason"></p></div>
                    <dl><div><dt>Duplicate deliveries</dt><dd id="metric-duplicates">0</dd></div><div><dt>Failed queue attempts</dt><dd id="metric-failures">0</dd></div><div><dt>Severity</dt><dd id="incident-severity">...</dd></div></dl>
                </div>
                <p class="evidence-note" id="synthetic-disclosure"></p>
            </section>

            <section class="screen-panel" data-panel="incident">
                <div class="section-heading"><div><p class="eyebrow">02 / Customer Incident</p><h2>One correlation trail, from cash to lockout.</h2></div><code id="correlation-id">...</code></div>
                <div class="timeline" id="timeline"></div>
            </section>

            <section class="screen-panel" data-panel="root">
                <div class="section-heading"><div><p class="eyebrow">03 / Root Cause</p><h2>The payment was healthy. Provisioning was not.</h2></div></div>
                <div class="root-grid">
                    <article><span>01</span><h3>Duplicate delivery</h3><p>At-least-once delivery sent the same provider event ten times. The persistent inbox retained every delivery.</p><strong id="root-deliveries">10 deliveries / 1 event</strong></article>
                    <article><span>02</span><h3>Failed queue job</h3><p>The real entitlement job exhausted its bounded retry policy after deterministic synthetic timeouts.</p><strong id="root-attempts">3 attempts / 3 failures</strong></article>
                    <article><span>03</span><h3>Slow access</h3><p>The successful payment remained disconnected from access long enough to breach the configured entitlement SLA.</p><strong id="root-sla">SLA breached</strong></article>
                </div>
                <div class="root-statement"><p class="eyebrow">Correlated root cause</p><p id="root-cause"></p></div>
            </section>

            <section class="screen-panel" data-panel="recovery">
                <div class="section-heading"><div><p class="eyebrow">04 / Safe Recovery</p><h2>Repair once. Prove the repeat is harmless.</h2></div><button class="primary-button" id="reconcile-button" type="button">Run safe reconciliation</button></div>
                <div class="recovery-grid">
                    <article><p class="eyebrow">Before</p><div id="before-state" class="state-card empty">No reconciliation run yet.</div></article>
                    <article><p class="eyebrow">After</p><div id="after-state" class="state-card empty">Awaiting lock-protected repair.</div></article>
                </div>
                <div class="audit-register"><div class="section-heading compact"><div><p class="eyebrow">Immutable audit history</p><h3>Reconciliation actions</h3></div></div><div id="reconciliation-list"></div></div>
            </section>

            <section class="screen-panel" data-panel="comparison">
                <div class="section-heading"><div><p class="eyebrow">05 / Fragile vs Stabilised</p><h2>Same fixture. Different engineering controls.</h2></div></div>
                <div class="comparison-grid" id="comparison-grid"></div>
            </section>

            <section class="screen-panel" data-panel="engineering">
                <div class="section-heading"><div><p class="eyebrow">06 / Engineering Evidence</p><h2>Claims resolve to executable evidence.</h2></div></div>
                <div class="engineering-grid" id="engineering-grid"></div>
                <div class="engineering-callout"><strong>Release boundary</strong><p>This lab uses synthetic data and a deterministic failure adapter. It demonstrates application-level effectively-once business effects, not distributed exactly-once delivery or real recovered customer revenue.</p></div>
            </section>
        </main>

        <footer class="page-width"><span>Laravel / MySQL / Valkey / Queue worker</span><span>Local synthetic evidence only</span></footer>
        <div class="toast" id="toast" role="status" aria-live="polite"></div>
    </div>
</body>
</html>
