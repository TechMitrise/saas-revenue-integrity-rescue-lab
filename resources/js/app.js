const root = document.querySelector('#dashboard-app');

if (root) {
    let dashboard;
    let toastTimer;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const money = (value) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value ?? 0);
    const duration = (milliseconds) => milliseconds >= 60000 ? `${Math.round(milliseconds / 60000)} min` : `${milliseconds} ms`;
    const escapeHtml = (value) => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');

    function toast(message, error = false) {
        clearTimeout(toastTimer);
        const element = document.querySelector('#toast');
        element.textContent = message;
        element.className = `toast visible ${error ? 'error' : ''}`;
        toastTimer = setTimeout(() => element.classList.remove('visible'), 3200);
    }

    async function request(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf, ...(options.body ? { 'Content-Type': 'application/json' } : {}), ...options.headers },
        });
        const body = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(body.message ?? 'Request failed');
        return body;
    }

    function stateMarkup(state) {
        if (!state) return '<span>Not recorded</span>';
        return Object.entries(state).map(([key, value]) => `<div><span>${escapeHtml(key.replaceAll('_', ' '))}</span><strong>${escapeHtml(value)}</strong></div>`).join('');
    }

    function renderControlRoom() {
        const { metrics, incident } = dashboard;
        document.querySelector('#guarantee').textContent = dashboard.guarantee;
        document.querySelector('#synthetic-disclosure').textContent = dashboard.syntheticDisclosure;
        document.querySelector('#metric-customer').textContent = metrics.customer;
        document.querySelector('#metric-revenue').textContent = money(metrics.revenueAtRisk);
        document.querySelector('#metric-delay').textContent = duration(metrics.paymentToAccessMs);
        document.querySelector('#metric-sla').textContent = `${duration(incident.evidence.sla_threshold_ms)} SLA`;
        document.querySelector('#metric-access').textContent = metrics.accessState;
        document.querySelector('#metric-duplicates').textContent = metrics.duplicateDeliveries;
        document.querySelector('#metric-failures').textContent = metrics.queueFailures;
        document.querySelector('#incident-state').textContent = metrics.status;
        document.querySelector('#incident-state').className = `incident-state ${metrics.status}`;
        document.querySelector('#incident-title').textContent = incident.title;
        document.querySelector('#incident-reason').textContent = incident.reason;
        document.querySelector('#incident-severity').textContent = incident.severity;
        document.querySelector('#correlation-id').textContent = incident.evidence.correlation_id;
        document.querySelector('#root-cause').textContent = incident.rootCause;
        document.querySelector('#root-deliveries').textContent = `${incident.evidence.provider_delivery_ids.length} deliveries / 1 provider event`;
        document.querySelector('#root-attempts').textContent = `${incident.evidence.job_uuids.length} attempts / ${incident.evidence.queue_failures} failures`;
        document.querySelector('#root-sla').textContent = `${duration(incident.evidence.payment_to_access_ms)} elapsed / ${duration(incident.evidence.sla_threshold_ms)} SLA`;
    }

    function renderTimeline() {
        document.querySelector('#timeline').innerHTML = dashboard.timeline.map((item, index) => `
            <article class="timeline-item ${escapeHtml(item.state)}">
                <span class="timeline-index">${String(index + 1).padStart(2, '0')}</span>
                <div><p class="eyebrow">${escapeHtml(item.kind)}</p><h3>${escapeHtml(item.title)}</h3><code>${escapeHtml(item.detail)}</code></div>
                <time>${escapeHtml(new Date(item.at).toLocaleString())}</time>
            </article>`).join('');
    }

    function renderRecovery() {
        const actions = dashboard.reconciliations;
        const repaired = actions.find((action) => action.result === 'repaired');
        document.querySelector('#before-state').className = `state-card ${repaired ? '' : 'empty'}`;
        document.querySelector('#after-state').className = `state-card ${repaired ? '' : 'empty'}`;
        document.querySelector('#before-state').innerHTML = repaired ? stateMarkup(repaired.before) : 'No reconciliation run yet.';
        document.querySelector('#after-state').innerHTML = repaired ? stateMarkup(repaired.after) : 'Awaiting lock-protected repair.';
        document.querySelector('#reconciliation-list').innerHTML = actions.length ? actions.map((action) => `
            <article class="audit-row"><span class="result-badge ${escapeHtml(action.result)}">${escapeHtml(action.result)}</span><div><strong>${escapeHtml(action.id)}</strong><p>${escapeHtml(action.reason)}</p></div><b>${action.businessEffects} effect(s)</b></article>`).join('') : '<p class="empty-copy">No reconciliation actions yet.</p>';
        const button = document.querySelector('#reconcile-button');
        button.textContent = actions.length === 0 ? 'Run safe reconciliation' : actions.some((action) => action.result === 'no_op') ? 'No-op verified' : 'Verify repeat no-op';
        button.disabled = actions.some((action) => action.result === 'no_op');
    }

    function renderComparison() {
        document.querySelector('#comparison-grid').innerHTML = dashboard.comparison.map((run) => `
            <article class="comparison-card ${escapeHtml(run.mode)}">
                <div class="comparison-head"><div><p class="eyebrow">${escapeHtml(run.mode)} path</p><h3>${run.mode === 'fragile' ? 'Revenue path breaks' : 'Business effect holds'}</h3></div><span>${escapeHtml(run.entitlementState)}</span></div>
                <dl>
                    <div><dt>Webhook deliveries</dt><dd>${run.webhookDeliveries}</dd></div><div><dt>Provider events</dt><dd>${run.providerEvents}</dd></div>
                    <div><dt>Business actions</dt><dd>${run.businessActions}</dd></div><div><dt>Queue failures</dt><dd>${run.queueFailures}</dd></div>
                    <div><dt>Payment to access</dt><dd>${duration(run.paymentToAccessMs)}</dd></div><div><dt>SLA</dt><dd>${run.slaBreached ? 'Breached' : 'Within target'}</dd></div>
                    <div><dt>Observed queries</dt><dd>${run.queryCount}</dd></div><div><dt>Lab processing</dt><dd>${run.processingMs} ms</dd></div>
                </dl>
                <div class="control-list">${run.controls.map((control) => `<span>${escapeHtml(control)}</span>`).join('')}</div>
                <code>${escapeHtml(run.correlationId)}</code>
            </article>`).join('');
    }

    function renderEngineering() {
        document.querySelector('#engineering-grid').innerHTML = dashboard.engineering.map((item) => `
            <article><p class="eyebrow">Executable reference</p><h3>${escapeHtml(item.label)}</h3><code>${escapeHtml(item.path)}</code><span>${escapeHtml(item.command)}</span></article>`).join('');
    }

    function render() {
        renderControlRoom();
        renderTimeline();
        renderRecovery();
        renderComparison();
        renderEngineering();
    }

    async function load() {
        dashboard = await request('/api/dashboard');
        render();
    }

    document.querySelectorAll('.journey-step').forEach((button) => button.addEventListener('click', () => {
        document.querySelectorAll('.journey-step').forEach((candidate) => candidate.classList.toggle('active', candidate === button));
        document.querySelectorAll('.screen-panel').forEach((panel) => panel.classList.toggle('active', panel.dataset.panel === button.dataset.screen));
        document.querySelector('#screen-status').textContent = `${button.textContent.trim()} opened`;
    }));

    document.querySelector('#reconcile-button').addEventListener('click', async () => {
        const button = document.querySelector('#reconcile-button');
        button.disabled = true;
        try {
            const result = await request(`/api/incidents/${dashboard.incident.id}/reconcile`, { method: 'POST' });
            dashboard = result.dashboard;
            render();
            toast(result.action.result === 'repaired' ? 'Access restored with one business effect' : 'Repeat reconciliation verified as a no-op');
        } catch (error) {
            button.disabled = false;
            toast(error.message, true);
        }
    });

    document.querySelector('#reset-button').addEventListener('click', async () => {
        if (!window.confirm('Replay the complete synthetic fragile and stabilised scenario?')) return;
        try {
            const result = await request('/api/demo/reset', { method: 'POST' });
            dashboard = result.dashboard;
            render();
            toast('Synthetic scenario replayed');
        } catch (error) { toast(error.message, true); }
    });

    load().catch((error) => toast(error.message, true));
}
