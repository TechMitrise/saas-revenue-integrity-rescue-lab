const root = document.querySelector('.xray-page');

if (root) {
    const guidedSteps = [...document.querySelectorAll('[data-guided-step]')];
    let guidedIndex = 0;

    function showGuided(index) {
        guidedIndex = Math.max(0, Math.min(index, guidedSteps.length - 1));
        guidedSteps.forEach((step, candidate) => step.classList.toggle('active', candidate === guidedIndex));
        document.querySelector('[data-guided-count]').textContent = `${guidedIndex + 1} / ${guidedSteps.length}`;
    }

    document.querySelector('[data-guided-prev]')?.addEventListener('click', () => showGuided(guidedIndex - 1));
    document.querySelector('[data-guided-next]')?.addEventListener('click', () => showGuided(guidedIndex + 1));

    document.querySelectorAll('[data-stage]').forEach((button) => {
        button.addEventListener('click', () => {
            const stage = button.dataset.stage;
            document.querySelectorAll('[data-stage]').forEach((candidate) => candidate.classList.toggle('active', candidate === button));
            document.querySelectorAll('[data-stage-panel]').forEach((panel) => panel.classList.toggle('active', panel.dataset.stagePanel === stage));
            document.querySelectorAll('[data-stage-event]').forEach((event) => event.classList.toggle('highlight', event.dataset.stageEvent === stage));
        });
    });

    document.querySelectorAll('[data-severity-filter]').forEach((button) => {
        button.addEventListener('click', () => {
            const severity = button.dataset.severityFilter;
            document.querySelectorAll('[data-severity-filter]').forEach((candidate) => candidate.classList.toggle('active', candidate === button));
            document.querySelectorAll('[data-severity]').forEach((finding) => {
                finding.hidden = severity !== 'all' && finding.dataset.severity !== severity;
            });
        });
    });

    document.querySelector('[data-print]')?.addEventListener('click', () => window.print());
}
