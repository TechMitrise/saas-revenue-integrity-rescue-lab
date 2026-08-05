import { expect, test } from '@playwright/test';

test('operator traces, repairs, verifies and compares the paid-access rescue', async ({ page }) => {
    await page.goto('/login');
    await expect(page.getByRole('heading', { name: 'Enter the lab.' })).toBeVisible();
    await page.getByRole('button', { name: /Open the Control Room/ }).click();
    await expect(page).toHaveURL(/\/app$/);

    page.once('dialog', (dialog) => dialog.accept());
    const replayed = page.waitForResponse((response) => response.url().endsWith('/api/demo/reset') && response.ok());
    await page.getByRole('button', { name: 'Replay scenario' }).click();
    await replayed;

    await expect(page.locator('#incident-title')).toHaveText('Payment succeeded. Access failed.');
    await expect(page.locator('#metric-customer')).toHaveText('Northstar Labs');
    await expect(page.locator('#metric-revenue')).toHaveText('$4,800');
    await expect(page.locator('#metric-access')).toHaveText('locked');

    await page.getByRole('button', { name: /Customer Incident/ }).click();
    await expect(page.getByRole('heading', { name: /One correlation trail/ })).toBeVisible();
    await expect(page.locator('.timeline-item')).toHaveCount(6);

    await page.getByRole('button', { name: /Root Cause/ }).click();
    await expect(page.locator('#root-deliveries')).toHaveText('10 deliveries / 1 provider event');
    await expect(page.locator('#root-attempts')).toHaveText('3 attempts / 3 failures');

    await page.getByRole('button', { name: /Safe Recovery/ }).click();
    const repaired = page.waitForResponse((response) => response.url().includes('/reconcile') && response.ok());
    await page.getByRole('button', { name: 'Run safe reconciliation' }).click();
    await repaired;
    await expect(page.locator('#after-state')).toContainText('active');
    await expect(page.getByRole('button', { name: 'Verify repeat no-op' })).toBeEnabled();

    const noOp = page.waitForResponse((response) => response.url().includes('/reconcile') && response.ok());
    await page.getByRole('button', { name: 'Verify repeat no-op' }).click();
    await noOp;
    await expect(page.getByRole('button', { name: 'No-op verified' })).toBeDisabled();
    await expect(page.locator('#reconciliation-list')).toContainText('no_op');
    await expect(page.locator('#reconciliation-list')).toContainText('0 effect(s)');

    await page.getByRole('button', { name: /Fragile vs Stabilised/ }).click();
    await expect(page.locator('.comparison-card')).toHaveCount(2);
    await expect(page.locator('.comparison-card.fragile')).toContainText('Revenue path breaks');
    await expect(page.locator('.comparison-card.stabilised')).toContainText('Business effect holds');

    await page.getByRole('button', { name: /Engineering Evidence/ }).click();
    await expect(page.getByRole('heading', { name: /Claims resolve to executable evidence/ })).toBeVisible();
    await expect(page.locator('#engineering-grid')).toContainText('./vendor/bin/sail test');
});

test('operator builds and rescues the incident one observable action at a time', async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('button', { name: /Open the Control Room/ }).click();
    await expect(page).toHaveURL(/\/app$/);

    const runGuide = async (endpoint) => {
        const completed = page.waitForResponse((response) => response.url().endsWith(endpoint) && response.ok());
        await page.locator('#guide-action').click();
        await completed;
    };

    await runGuide('/api/demo/guided/start');
    await expect(page.locator('#guide-progress')).toHaveText('1 / 8 actions completed');
    await expect(page.locator('.guide-step').nth(0)).toContainText('succeeded / USD 4,800');

    await runGuide('/api/demo/guided/advance');
    await expect(page.locator('#guide-progress')).toHaveText('2 / 8 actions completed');
    await expect(page.locator('.guide-step').nth(1)).toContainText('10 deliveries / 1 event / 1 effect');

    for (const completed of [3, 4, 5]) {
        await runGuide('/api/demo/guided/advance');
        await expect(page.locator('#guide-progress')).toHaveText(`${completed} / 8 actions completed`);
    }
    await expect(page.locator('.guide-step').nth(4)).toContainText('failed');

    await runGuide('/api/demo/guided/advance');
    await expect(page.locator('#guide-progress')).toHaveText('6 / 8 actions completed');
    await expect(page.locator('.guide-step').nth(5)).toContainText('open / USD 4,800');

    await runGuide('/api/demo/guided/advance');
    await expect(page.locator('#guide-progress')).toHaveText('7 / 8 actions completed');
    await expect(page.locator('.guide-step').nth(6)).toContainText('active / 1 effect');

    await runGuide('/api/demo/guided/advance');
    await expect(page.locator('#guide-progress')).toHaveText('8 / 8 actions completed');
    await expect(page.locator('#guide-status')).toHaveText('Evidence complete');
    await expect(page.locator('.guide-step').nth(7)).toContainText('no_op / 0 effects');
    await expect(page.locator('#guide-facts')).toContainText('active');
    await expect(page.locator('#guide-facts')).toContainText('1');
});
