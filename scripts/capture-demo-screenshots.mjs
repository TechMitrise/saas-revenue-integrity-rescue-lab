import { chromium } from '@playwright/test';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';

const root = process.cwd();
const baseURL = process.env.E2E_BASE_URL ?? 'http://localhost';
const output = path.join(root, 'public', 'images', 'proof');

await mkdir(output, { recursive: true });

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await context.newPage();

async function waitForApi(pathname, action) {
    const response = page.waitForResponse((candidate) => candidate.url().includes(pathname) && candidate.ok());
    await action();
    await response;
}

async function openScreen(name, panel) {
    await page.getByRole('button', { name }).click();
    await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
    await page.locator(`[data-panel="${panel}"]`).waitFor({ state: 'visible' });
    await page.waitForTimeout(250);
}

async function capture(filename) {
    await page.screenshot({ path: path.join(output, filename), animations: 'disabled' });
}

try {
    await page.goto(`${baseURL}/login`, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: /Open the Control Room/ }).click();
    await page.waitForURL(/\/app$/);

    page.once('dialog', (dialog) => dialog.accept());
    await waitForApi('/api/demo/reset', () => page.getByRole('button', { name: 'Replay scenario' }).click());

    await page.addStyleTag({ content: `
        .hero, .guided-demo, footer.page-width, .toast { display: none !important; }
        .topbar { position: relative; min-height: 76px; background: #f2ecdf; }
        main.page-width { padding-top: 24px; }
        .journey-nav { margin-bottom: 24px; }
        .journey-step { padding-block: 12px; }
        .screen-panel { min-height: 0; margin-bottom: 20px; }
        .section-heading { margin-bottom: 20px; }
        .section-heading h2 { font-size: 46px; }
        .metric-grid article { min-height: 142px; padding-block: 20px; }
        .metric-grid strong { margin-block: 15px 9px; font-size: 42px; }
        .incident-brief { margin-top: 24px; padding: 25px 30px; }
        .incident-brief h3 { font-size: 34px; }
        .timeline-item { min-height: 78px; padding-block: 9px; }
        .timeline-item h3 { margin-bottom: 3px; font-size: 20px; }
        .root-grid article { min-height: 290px; padding: 28px; }
        .root-grid h3 { margin-top: 38px; }
        .root-statement { margin-top: 18px; padding: 22px 28px; }
        .state-card { min-height: 175px; }
        .state-card div { padding-block: 9px; }
        .audit-register { margin-top: 16px; padding: 22px; }
        .audit-row { padding-block: 12px; }
        .comparison-card { padding: 25px; }
        .comparison-card dl { margin-block: 20px; }
        .comparison-card dl div { padding-block: 8px; }
    ` });

    await openScreen(/Control Room/, 'control');
    await capture('01-rescue-control-room.png');

    await openScreen(/Customer Incident/, 'incident');
    await capture('02-customer-incident-timeline.png');

    await openScreen(/Root Cause/, 'root');
    await capture('03-root-cause-evidence.png');

    await openScreen(/Safe Recovery/, 'recovery');
    await waitForApi('/reconcile', () => page.getByRole('button', { name: 'Run safe reconciliation' }).click());
    await capture('04-safe-recovery-before-after.png');

    await waitForApi('/reconcile', () => page.getByRole('button', { name: 'Verify repeat no-op' }).click());
    await capture('05-repeat-no-op-proof.png');

    page.once('dialog', (dialog) => dialog.accept());
    await waitForApi('/api/demo/reset', () => page.getByRole('button', { name: 'Replay scenario' }).click());
    await openScreen(/Fragile vs Stabilised/, 'comparison');
    await capture('06-fragile-vs-stabilised.png');
} finally {
    await context.close();
    await browser.close();
}

console.log(`Six proof screenshots: ${output}`);
