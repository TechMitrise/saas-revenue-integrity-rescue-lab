const root = document.querySelector('#dashboard-app');

if (root) {
    const state = { dashboard: null, selectedId: null, filter: 'active' };
    const elements = {
        activityList: document.querySelector('#activity-list'),
        detail: document.querySelector('#incident-detail'),
        incidentList: document.querySelector('#incident-list'),
        lastScan: document.querySelector('#last-scan'),
        queueTotal: document.querySelector('#queue-total'),
        riskBreakdown: document.querySelector('#risk-breakdown'),
        scanButton: document.querySelector('#scan-button'),
        scanStatus: document.querySelector('#scan-status'),
        toast: document.querySelector('#toast'),
    };
    const labels = {
        canceled_billing: 'Post-cancel billing', duplicate_invoice: 'Duplicate invoice', missing_invoice: 'Missing invoice',
        overdue_invoice: 'Overdue collection', payment_failure: 'Payment failure', underbilling: 'Underbilling',
    };
    const assignees = ['Maya Chen', 'Eli Turner', 'Noah Williams'];
    let toastTimer;

    const escapeHtml = (value) => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    const formatMoney = (value, compact = false) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0, notation: compact && value >= 10000 ? 'compact' : 'standard' }).format(value);
    const formatTime = (value) => value ? new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }).format(new Date(value)) : 'No scan completed';

    function showToast(message, type = 'success') {
        clearTimeout(toastTimer);
        elements.toast.textContent = message;
        elements.toast.className = `toast visible ${type === 'error' ? 'error' : ''}`;
        toastTimer = setTimeout(() => elements.toast.classList.remove('visible'), 3200);
    }

    async function request(url, options = {}) {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const response = await fetch(url, {
            ...options,
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token, ...(options.body ? { 'Content-Type': 'application/json' } : {}), ...options.headers },
        });
        const body = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(body.message ?? body.error ?? 'Request failed');
        return body;
    }

    function filteredIncidents() {
        const incidents = state.dashboard?.incidents ?? [];
        if (state.filter === 'all') return incidents;
        if (state.filter === 'active') return incidents.filter((incident) => ['open', 'investigating'].includes(incident.status));
        return incidents.filter((incident) => incident.status === state.filter);
    }

    function renderMetrics() {
        const metrics = state.dashboard.metrics;
        document.querySelector('#metric-risk').textContent = formatMoney(metrics.atRisk, true);
        document.querySelector('#metric-incidents').textContent = metrics.activeIncidents;
        document.querySelector('#metric-mrr').textContent = formatMoney(metrics.totalMrr, true);
        document.querySelector('#metric-accounts').textContent = metrics.monitoredAccounts;
        document.querySelector('#metric-recovered').textContent = formatMoney(metrics.recovered, true);
        document.querySelector('#metric-health').textContent = `${metrics.healthyRate}%`;
        elements.lastScan.textContent = `Last audit ${formatTime(state.dashboard.lastAuditAt)}`;
        elements.scanStatus.textContent = metrics.activeIncidents ? `${metrics.activeIncidents} issues need attention` : 'Billing is in good shape';
    }

    function renderIncidents() {
        const incidents = filteredIncidents();
        elements.queueTotal.textContent = incidents.length;
        elements.incidentList.innerHTML = incidents.length ? incidents.map((incident) => `
            <button class="incident-card ${incident.id === state.selectedId ? 'selected' : ''}" data-incident-id="${incident.id}" type="button">
                <span class="severity-mark ${escapeHtml(['recovered', 'dismissed'].includes(incident.status) ? incident.status : incident.severity)}"></span>
                <span><span class="incident-account">${escapeHtml(incident.accountName)}</span><span class="incident-title">${escapeHtml(incident.title)}</span><span class="incident-meta">${escapeHtml(incident.status)} / ${escapeHtml(labels[incident.type] ?? incident.type.replaceAll('_', ' '))}</span></span>
                <span class="incident-amount"><strong>${formatMoney(incident.revenueImpact)}</strong><small>Exposure</small></span>
            </button>`).join('') : '<p class="no-incidents">No incidents match this view.</p>';
    }

    function renderDetail() {
        const incident = state.dashboard.incidents.find((candidate) => candidate.id === state.selectedId);
        if (!incident) {
            elements.detail.innerHTML = '<div class="detail-empty"><span class="empty-orbit"></span><h2>Select an incident</h2><p>Choose an item from the rescue queue to review evidence and take action.</p></div>';
            return;
        }
        const statusClass = ['recovered', 'dismissed'].includes(incident.status) ? incident.status : incident.severity;
        elements.detail.innerHTML = `
            <div class="detail-topline"><span class="badge ${escapeHtml(statusClass)}">${escapeHtml(incident.severity)} severity</span><span class="detail-id">INC-${String(incident.id).padStart(4, '0')}</span></div>
            <h2>${escapeHtml(incident.title)}</h2><p class="detail-account">${escapeHtml(incident.accountName)}</p>
            <div class="impact-strip"><div><span>Revenue exposure</span><strong>${formatMoney(incident.revenueImpact)}</strong></div><div><span>Current status</span><strong>${escapeHtml(incident.status)}</strong></div></div>
            <div class="evidence-block"><h3>Why it was flagged</h3><p>${escapeHtml(incident.reason)}</p></div>
            <div class="action-block"><h3>Recommended rescue</h3><p>${escapeHtml(incident.recommendedAction)}</p></div>
            <div class="controls-row">
                <label><span class="sr-only">Incident status</span><select class="status-control" id="status-control">${['open', 'investigating', 'recovered', 'dismissed'].map((status) => `<option value="${status}" ${incident.status === status ? 'selected' : ''}>${status[0].toUpperCase()}${status.slice(1)}</option>`).join('')}</select></label>
                <label><span class="sr-only">Assignee</span><select class="assignee-control" id="assignee-control"><option value="">Unassigned</option>${assignees.map((name) => `<option value="${escapeHtml(name)}" ${incident.assignee === name ? 'selected' : ''}>${escapeHtml(name)}</option>`).join('')}</select></label>
            </div>
            <div class="notes-block"><h3>Operator notes</h3><form class="note-form" id="note-form"><input name="note" maxlength="1000" placeholder="Add investigation context..." aria-label="New incident note" required><button type="submit">Add note</button></form><div class="notes-list">${incident.notes.length ? incident.notes.map((note) => `<p class="note"><b>${escapeHtml(note.author)}</b> / ${escapeHtml(formatTime(note.at))}<br>${escapeHtml(note.body)}</p>`).join('') : '<p class="no-notes">No notes yet.</p>'}</div></div>`;
        document.querySelector('#status-control').addEventListener('change', (event) => updateIncident({ status: event.target.value }));
        document.querySelector('#assignee-control').addEventListener('change', (event) => updateIncident({ assignee: event.target.value || null }));
        document.querySelector('#note-form').addEventListener('submit', addNote);
    }

    function renderBreakdown() {
        const breakdown = state.dashboard.breakdown;
        if (!breakdown.length) { elements.riskBreakdown.innerHTML = '<p class="no-incidents">No active exposure remains.</p>'; return; }
        const max = Math.max(...breakdown.map((item) => item.impact));
        elements.riskBreakdown.innerHTML = breakdown.map((item) => `<div class="risk-row"><span class="risk-label"><strong>${escapeHtml(labels[item.type] ?? item.type)}</strong><small>${item.count} ${item.count === 1 ? 'incident' : 'incidents'}</small></span><span class="risk-track"><span class="risk-fill" style="width:${Math.max(5, Math.round((item.impact / max) * 100))}%"></span></span><span class="risk-amount">${formatMoney(item.impact, true)}</span></div>`).join('');
    }

    function renderActivity() {
        elements.activityList.innerHTML = state.dashboard.activity.length ? state.dashboard.activity.map((item) => `<li><time>${escapeHtml(formatTime(item.at))}</time>${escapeHtml(item.message)}</li>`).join('') : '<li>No activity yet.</li>';
    }

    function render() { renderMetrics(); renderIncidents(); renderDetail(); renderBreakdown(); renderActivity(); }

    async function loadDashboard({ preserveSelection = true } = {}) {
        state.dashboard = await request('/api/dashboard');
        const visible = filteredIncidents();
        if (!preserveSelection || !state.dashboard.incidents.some((incident) => incident.id === state.selectedId)) state.selectedId = visible[0]?.id ?? null;
        render();
    }

    async function updateIncident(patch) {
        try {
            await request(`/api/incidents/${state.selectedId}`, { method: 'PATCH', body: JSON.stringify(patch) });
            await loadDashboard(); showToast('Incident updated');
        } catch (error) { showToast(error.message, 'error'); await loadDashboard(); }
    }

    async function addNote(event) {
        event.preventDefault();
        const note = new FormData(event.currentTarget).get('note');
        try {
            await request(`/api/incidents/${state.selectedId}/notes`, { method: 'POST', body: JSON.stringify({ body: note }) });
            await loadDashboard(); showToast('Note added');
        } catch (error) { showToast(error.message, 'error'); }
    }

    async function runScan() {
        elements.scanButton.disabled = true; elements.scanButton.querySelector('span').textContent = 'Scanning billing records...';
        try {
            const result = await request('/api/audits', { method: 'POST' });
            state.dashboard = result.dashboard; render();
            showToast(`Scan complete: ${result.summary.detected} anomalies, ${result.summary.created} new`);
        } catch (error) { showToast(error.message, 'error'); }
        finally { elements.scanButton.disabled = false; elements.scanButton.querySelector('span').textContent = 'Run integrity scan'; }
    }

    async function resetLab() {
        if (!window.confirm('Reset all incident statuses and notes to the original lab scenario?')) return;
        try {
            const result = await request('/api/demo/reset', { method: 'POST' });
            state.dashboard = result.dashboard; state.filter = 'active'; state.selectedId = filteredIncidents()[0]?.id ?? null;
            document.querySelectorAll('.filter').forEach((button) => button.classList.toggle('active', button.dataset.filter === 'active'));
            render(); showToast('Lab scenario reset');
        } catch (error) { showToast(error.message, 'error'); }
    }

    elements.incidentList.addEventListener('click', (event) => {
        const card = event.target.closest('[data-incident-id]');
        if (!card) return;
        state.selectedId = Number(card.dataset.incidentId); renderIncidents(); renderDetail();
        if (window.innerWidth < 720) elements.detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    document.querySelectorAll('.filter').forEach((button) => button.addEventListener('click', () => {
        state.filter = button.dataset.filter;
        document.querySelectorAll('.filter').forEach((candidate) => candidate.classList.toggle('active', candidate === button));
        const visible = filteredIncidents();
        if (!visible.some((incident) => incident.id === state.selectedId)) state.selectedId = visible[0]?.id ?? null;
        renderIncidents(); renderDetail();
    }));
    elements.scanButton.addEventListener('click', runScan);
    document.querySelector('#reset-button')?.addEventListener('click', resetLab);
    document.querySelector('#mobile-reset-button')?.addEventListener('click', resetLab);
    loadDashboard({ preserveSelection: false }).catch((error) => { elements.incidentList.innerHTML = '<p class="no-incidents">The workspace could not be loaded.</p>'; showToast(error.message, 'error'); });
}
