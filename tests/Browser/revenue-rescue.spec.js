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
