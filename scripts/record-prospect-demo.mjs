import { chromium } from '@playwright/test';
import { mkdir, readdir, rm } from 'node:fs/promises';
import { spawnSync } from 'node:child_process';
import path from 'node:path';

const root = process.cwd();
const artifacts = path.join(root, 'artifacts');
const rawVideos = path.join(artifacts, 'raw');
const output = path.join(artifacts, 'rescue-lab-prospect-demo.mp4');
const poster = path.join(artifacts, 'rescue-lab-poster.png');
const baseURL = process.env.E2E_BASE_URL ?? 'http://localhost';

const pause = (milliseconds) => new Promise((resolve) => setTimeout(resolve, milliseconds));

async function caption(page, eyebrow, title, detail) {
    await page.evaluate(({ eyebrow, title, detail }) => {
        let card = document.querySelector('#recording-caption');

        if (! card) {
            card = document.createElement('aside');
            card.id = 'recording-caption';
            card.innerHTML = '<span></span><strong></strong><p></p>';
            document.body.append(card);

            const style = document.createElement('style');
            style.textContent = `
                #recording-caption {
                    position: fixed; left: 30px; bottom: 28px; z-index: 99999;
                    width: min(520px, calc(100vw - 60px)); padding: 18px 22px;
                    color: #f7f1e5; background: rgba(13, 35, 30, .94);
                    border: 1px solid rgba(238, 184, 65, .55); border-radius: 3px;
                    box-shadow: 0 18px 50px rgba(13, 35, 30, .22);
                    font-family: Georgia, serif; pointer-events: none;
                }
                #recording-caption span {
                    display: block; margin-bottom: 5px; color: #eeb841;
                    font: 700 11px/1.3 ui-sans-serif, sans-serif;
                    letter-spacing: .18em; text-transform: uppercase;
                }
                #recording-caption strong { display: block; font-size: 25px; line-height: 1.08; }
                #recording-caption p { margin: 7px 0 0; color: #c7d0cb; font: 500 14px/1.45 ui-sans-serif, sans-serif; }
            `;
            document.head.append(style);
        }

        card.querySelector('span').textContent = eyebrow;
        card.querySelector('strong').textContent = title;
        card.querySelector('p').textContent = detail;
    }, { eyebrow, title, detail });
}

async function openScreen(page, name, panel, copy) {
    await page.getByRole('button', { name }).click();
    await page.locator(`[data-panel="${panel}"]`).scrollIntoViewIfNeeded();
    await caption(page, ...copy);
    await pause(5000);
}

async function runGuide(page, endpoint, copy, wait = 3200) {
    const response = page.waitForResponse((candidate) => candidate.url().endsWith(endpoint) && candidate.ok());
    await page.locator('#guide-action').click();
    await response;
    await caption(page, ...copy);
    await pause(wait);
}

await rm(rawVideos, { recursive: true, force: true });
await mkdir(rawVideos, { recursive: true });

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    recordVideo: { dir: rawVideos, size: { width: 1440, height: 900 } },
});
const page = await context.newPage();

try {
    await page.goto(`${baseURL}/login`, { waitUntil: 'networkidle' });
    await caption(page, 'Revenue Integrity Rescue Lab', 'Cash arrived. Access did not.', 'A synthetic Laravel SaaS incident, traced from payment to entitlement.');
    await pause(4500);

    await page.getByRole('button', { name: /Open the Control Room/ }).click();
    await page.waitForURL(/\/app$/);

    await page.locator('#guided-demo').scrollIntoViewIfNeeded();
    await caption(page, 'Live incident replay', 'One click. One real application action.', 'Every step writes through the Laravel domain services and reloads its evidence from MySQL.');
    await pause(4000);

    await runGuide(page, '/api/demo/guided/start', [
        '01 / Payment',
        'USD 4,800 payment succeeded',
        'The subscription exists, but no webhook, entitlement or incident has been created yet.',
    ]);
    await runGuide(page, '/api/demo/guided/advance', [
        '02 / Duplicate webhook',
        'Ten deliveries. One provider event. One effect.',
        'The persistent inbox retains every signed delivery while the consumer advances the subscription once.',
    ], 4300);

    for (const attempt of [1, 2, 3]) {
        await runGuide(page, '/api/demo/guided/advance', [
            `0${attempt + 2} / Queue attempt ${attempt}`,
            'Controlled provisioning timeout persisted',
            `Attempt ${attempt} failed without creating another entitlement. The evidence remains inspectable.`,
        ], 2400);
    }

    await runGuide(page, '/api/demo/guided/advance', [
        '06 / Revenue integrity audit',
        'Paid-but-locked incident detected',
        'The SLA breach is translated into one affected account and USD 4,800 of synthetic monthly revenue exposure.',
    ], 4300);
    await page.screenshot({ path: poster });

    await runGuide(page, '/api/demo/guided/advance', [
        '07 / Safe reconciliation',
        'Access active. One business effect.',
        'A transaction, row locks and unique constraints repair the existing entitlement and retain before/after state.',
    ], 4300);
    await runGuide(page, '/api/demo/guided/advance', [
        '08 / Idempotency proof',
        'Repeat result: no_op',
        'The second reconciliation produces zero additional effects. Entitlement count remains one.',
    ], 4800);

    await openScreen(page, /Customer Incident/, 'incident', [
        'Evidence / Customer Incident',
        'One correlation trail',
        'Payment, provider event, duplicate deliveries, bounded retries and the SLA breach remain inspectable.',
    ]);

    await openScreen(page, /Root Cause/, 'root', [
        'Evidence / Root Cause',
        'At-least-once delivery met fragile provisioning',
        'Ten deliveries became one event and one business action, while the entitlement job failed three times.',
    ]);

    await page.getByRole('button', { name: /Safe Recovery/ }).click();
    await page.locator('[data-panel="recovery"]').scrollIntoViewIfNeeded();
    await caption(page, 'Evidence / Safe Recovery', 'Before, after and immutable actions', 'The repaired and no_op outcomes remain visible with their exact business-effect counts.');
    await pause(5000);

    await openScreen(page, /Fragile vs Stabilised/, 'comparison', [
        'Evidence / Before and after',
        'Same fixture. Different engineering controls.',
        'The fragile path stays locked; the stabilised path produces one active entitlement within SLA.',
    ]);

    await openScreen(page, /Engineering Evidence/, 'engineering', [
        'Evidence / Engineering',
        'Every claim resolves to executable proof',
        'PHPUnit, Playwright, CI, architecture, Horizon visibility and an operations runbook ship together.',
    ]);

    await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
    await caption(page, 'Accurate guarantee', 'At-least-once delivery. Effectively-once business effect.', 'Synthetic evidence only. Built to explain how we rescue and modernise revenue-critical Laravel systems.');
    await pause(6500);
} finally {
    await context.close();
    await browser.close();
}

const [recording] = (await readdir(rawVideos)).filter((file) => file.endsWith('.webm'));

if (! recording) {
    throw new Error('Playwright did not produce a recording.');
}

const conversion = spawnSync('ffmpeg', [
    '-y',
    '-i', path.join(rawVideos, recording),
    '-c:v', 'libx264',
    '-preset', 'medium',
    '-crf', '20',
    '-pix_fmt', 'yuv420p',
    '-movflags', '+faststart',
    output,
], { stdio: 'inherit' });

if (conversion.status !== 0) {
    throw new Error(`ffmpeg failed with exit code ${conversion.status}.`);
}

await rm(rawVideos, { recursive: true, force: true });
console.log(`Prospect video: ${output}`);
console.log(`Poster image: ${poster}`);
