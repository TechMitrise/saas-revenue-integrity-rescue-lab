const root = document.querySelector('.xray-page');

if (root) {
    const guidedSteps = [...document.querySelectorAll('[data-guided-step]')];
    const guidedJumpButtons = [...document.querySelectorAll('[data-guided-jump]')];
    const guidedMeter = document.querySelector('[data-guided-meter]');
    const guidedPrev = document.querySelector('[data-guided-prev]');
    const guidedNext = document.querySelector('[data-guided-next]');
    let guidedIndex = 0;

    function showGuided(index) {
        guidedIndex = Math.max(0, Math.min(index, guidedSteps.length - 1));
        guidedSteps.forEach((step, candidate) => {
            const active = candidate === guidedIndex;
            step.classList.toggle('active', active);
            step.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
        guidedJumpButtons.forEach((button, candidate) => {
            const active = candidate === guidedIndex;
            button.classList.toggle('active', active);
            button.setAttribute('aria-current', active ? 'step' : 'false');
        });
        document.querySelector('[data-guided-count]').textContent = `${guidedIndex + 1} / ${guidedSteps.length}`;
        if (guidedMeter) guidedMeter.style.width = `${((guidedIndex + 1) / guidedSteps.length) * 100}%`;
        if (guidedPrev) guidedPrev.disabled = guidedIndex === 0;
        if (guidedNext) guidedNext.disabled = guidedIndex === guidedSteps.length - 1;
    }

    guidedPrev?.addEventListener('click', () => showGuided(guidedIndex - 1));
    guidedNext?.addEventListener('click', () => showGuided(guidedIndex + 1));
    guidedJumpButtons.forEach((button) => {
        button.addEventListener('click', () => showGuided(Number(button.dataset.guidedJump)));
    });
    document.querySelector('[data-guided]')?.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            showGuided(guidedIndex + 1);
        }
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            showGuided(guidedIndex - 1);
        }
        if (event.key === 'Home') {
            event.preventDefault();
            showGuided(0);
        }
        if (event.key === 'End') {
            event.preventDefault();
            showGuided(guidedSteps.length - 1);
        }
    });

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
    showGuided(0);
}
