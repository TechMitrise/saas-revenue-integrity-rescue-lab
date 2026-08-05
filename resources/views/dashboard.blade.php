<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f3eddf">
    <meta name="description" content="Detect, prioritize, and resolve SaaS revenue integrity issues.">
    <title>Rescue Lab | Revenue Integrity</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-page">
    <div class="ambient ambient-one" aria-hidden="true"></div>
    <div class="ambient ambient-two" aria-hidden="true"></div>

    <div id="dashboard-app">
        <header class="topbar">
            <a class="brand" href="{{ route('dashboard') }}" aria-label="Revenue Integrity Rescue Lab home">
                <span class="brand-mark">RI</span>
                <span><strong>Rescue Lab</strong><small>Revenue integrity desk</small></span>
            </a>
            <div class="top-actions">
                <span class="live-indicator"><i></i> MySQL connected</span>
                <a class="quiet-button desktop-action" href="/api/incidents/export.csv">Export report</a>
                @env('local')<button class="quiet-button desktop-action" id="reset-button" type="button">Reset lab</button>@endenv
                <div class="user-chip">
                    <span>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role }}</small></div>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-button" type="submit">Sign out</button></form>
            </div>
        </header>

        <main class="page-width">
            <section class="hero reveal">
                <div class="hero-copy">
                    <p class="eyebrow">Revenue operations / August close</p>
                    <h1>Find the leak.<br><em>Rescue the revenue.</em></h1>
                    <p class="hero-description">A single operating view for billing anomalies, collection failures, and the work that turns risk back into revenue.</p>
                </div>
                <div class="scan-panel">
                    <div><span class="scan-label">Integrity engine</span><strong id="scan-status">Ready to scan</strong><small id="last-scan">Loading latest audit...</small></div>
                    <button class="scan-button" id="scan-button" type="button"><span>Run integrity scan</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 17 10-10M9 7h8v8"/></svg></button>
                </div>
            </section>

            <section class="metrics reveal" aria-label="Revenue integrity summary">
                <article class="metric-card metric-risk"><span>Revenue at risk</span><strong id="metric-risk">--</strong><small><i class="signal signal-red"></i><b id="metric-incidents">--</b> active incidents</small></article>
                <article class="metric-card"><span>Monitored MRR</span><strong id="metric-mrr">--</strong><small>Across <b id="metric-accounts">--</b> customer accounts</small></article>
                <article class="metric-card"><span>Recovered</span><strong id="metric-recovered">--</strong><small>This rescue session</small></article>
                <article class="metric-card metric-health"><span>Billing health</span><strong id="metric-health">--</strong><small>Clean subscription coverage</small></article>
            </section>

            <section class="workspace reveal">
                <div class="queue-panel">
                    <div class="section-heading"><div><p class="eyebrow">Rescue queue</p><h2>Integrity incidents</h2></div><span class="queue-total" id="queue-total">0</span></div>
                    <div class="filters" role="group" aria-label="Filter incidents">
                        <button class="filter active" data-filter="active" type="button">Active</button>
                        <button class="filter" data-filter="open" type="button">Open</button>
                        <button class="filter" data-filter="investigating" type="button">Investigating</button>
                        <button class="filter" data-filter="recovered" type="button">Recovered</button>
                        <button class="filter" data-filter="all" type="button">All</button>
                    </div>
                    <div class="incident-list" id="incident-list" aria-live="polite"><div class="loading-card"></div><div class="loading-card"></div><div class="loading-card"></div></div>
                </div>
                <aside class="detail-panel" id="incident-detail" aria-live="polite"><div class="detail-empty"><span class="empty-orbit"></span><h2>Select an incident</h2><p>Choose an item from the rescue queue to review evidence and take action.</p></div></aside>
            </section>

            <section class="lower-grid reveal">
                <article class="insight-panel"><div class="section-heading compact"><div><p class="eyebrow">Exposure map</p><h2>Risk by failure mode</h2></div></div><div id="risk-breakdown" class="risk-breakdown"></div></article>
                <article class="activity-panel"><div class="section-heading compact"><div><p class="eyebrow">Audit trail</p><h2>Recent activity</h2></div></div><ol class="activity-list" id="activity-list"></ol></article>
            </section>
        </main>

        <footer class="page-width"><span>Revenue Integrity Rescue Lab</span><span>Laravel / MySQL / Local data only</span><div class="mobile-actions"><a href="/api/incidents/export.csv">Export</a>@env('local')<button id="mobile-reset-button" type="button">Reset</button>@endenv<form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form></div></footer>
        <div class="toast" id="toast" role="status" aria-live="polite"></div>
    </div>
</body>
</html>
