import { expect, test } from '@playwright/test';
import { existsSync, readFileSync } from 'node:fs';

function envValue(key) {
    if (process.env[key]) return process.env[key];
    if (! existsSync('.env')) return null;

    const line = readFileSync('.env', 'utf8').split(/\r?\n/).find((candidate) => candidate.startsWith(`${key}=`));

    return line ? line.slice(key.length + 1).replace(/^["']|["']$/g, '') : null;
}

const enabled = envValue('CRITICAL_XRAY_SPECIMEN_ENABLED') === 'true';
const accessCode = envValue('CRITICAL_XRAY_ACCESS_CODE');

test.skip(! enabled || ! accessCode, 'Protected X-Ray specimen is not enabled for this browser run.');

test('protected X-Ray specimen unlocks, filters, links evidence and supports print layout', async ({ page }, testInfo) => {
    const route = '/critical-software-xray/sample/revenue-access-failure';
    const forwardedFor = testInfo.project.name.includes('mobile') ? '203.0.113.12' : '203.0.113.11';

    await page.setExtraHTTPHeaders({ 'X-Forwarded-For': forwardedFor });
    const access = await page.goto(`${route}/access`);

    expect(access.headers()['x-robots-tag']).toContain('noindex');
    await page.getByLabel('Access code').fill(accessCode);
    await page.getByRole('button', { name: 'Unlock specimen' }).click();
    await expect(page).toHaveURL(new RegExp(`${route}$`));
    await expect(page.getByRole('heading', { name: /Payment succeeded/ })).toBeVisible();
    await expect(page.getByRole('heading', { name: /Synthetic professional-service specimen/ })).toBeVisible();

    await page.locator('[data-stage="queue"]').click();
    await expect(page.locator('[data-stage-panel="queue"]')).toContainText('First divergence evidence');
    await expect(page.locator('[data-stage-panel="queue"]')).toContainText('3 failed attempts; deterministic timeout evidence retained.');

    await page.getByRole('button', { name: 'High' }).click();
    await expect(page.locator('#finding-F-02')).toBeVisible();
    await expect(page.locator('#finding-F-01')).toBeHidden();

    await expect(page.getByText('Paid + active subscription + locked entitlement')).toBeVisible();
    await expect(page.getByText('Existing entitlement active once')).toBeVisible();

    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth + 1);
    expect(overflow).toBe(false);

    await page.emulateMedia({ media: 'print' });
    await expect(page.locator('.xray-top')).toBeHidden();
});
