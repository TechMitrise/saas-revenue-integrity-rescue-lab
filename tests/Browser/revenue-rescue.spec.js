import { expect, test } from '@playwright/test';

test('operator completes a revenue rescue from sign-in through recovery', async ({ page }) => {
    await page.goto('/login');
    await expect(page.getByRole('heading', { name: 'Welcome back.' })).toBeVisible();

    await page.getByRole('button', { name: /Enter the rescue desk/ }).click();
    await expect(page).toHaveURL(/\/app$/);
    await expect(page.getByRole('heading', { name: /Find the leak/ })).toBeVisible();

    page.once('dialog', (dialog) => dialog.accept());
    const resetFinished = page.waitForResponse((response) => response.url().endsWith('/api/demo/reset') && response.ok());
    await page.getByRole('button', { name: /^Reset/ }).click();
    await resetFinished;

    await expect(page.locator('#metric-incidents')).toHaveText('6');
    await expect(page.locator('#queue-total')).toHaveText('6');

    await page.locator('[data-incident-id]').filter({ hasText: 'Northstar Labs' }).click();
    await expect(page.getByRole('heading', { name: 'High-value payment failed' })).toBeVisible();

    const investigatingSaved = page.waitForResponse((response) => response.request().method() === 'PATCH' && response.url().includes('/api/incidents/'));
    await page.locator('#status-control').selectOption('investigating');
    await investigatingSaved;
    await expect(page.locator('#status-control')).toHaveValue('investigating');

    const assigneeSaved = page.waitForResponse((response) => response.request().method() === 'PATCH' && response.url().includes('/api/incidents/'));
    await page.locator('#assignee-control').selectOption('Maya Chen');
    await assigneeSaved;
    await expect(page.locator('#assignee-control')).toHaveValue('Maya Chen');

    await page.getByLabel('New incident note').fill('Billing owner confirmed a card update.');
    const noteSaved = page.waitForResponse((response) => response.request().method() === 'POST' && response.url().endsWith('/notes'));
    await page.getByRole('button', { name: 'Add note' }).click();
    await noteSaved;
    await expect(page.locator('.notes-list')).toContainText('Billing owner confirmed a card update.');

    const recoverySaved = page.waitForResponse((response) => response.request().method() === 'PATCH' && response.url().includes('/api/incidents/'));
    await page.locator('#status-control').selectOption('recovered');
    await recoverySaved;
    await expect(page.locator('#metric-incidents')).toHaveText('5');
    await expect(page.locator('#metric-recovered')).toHaveText('$4,800');

    const auditFinished = page.waitForResponse((response) => response.request().method() === 'POST' && response.url().endsWith('/api/audits'));
    await page.getByRole('button', { name: 'Run integrity scan' }).click();
    await auditFinished;
    await expect(page.locator('#scan-status')).toContainText('5 issues');

    await page.getByRole('button', { name: 'Sign out' }).click();
    await expect(page).toHaveURL(/\/login$/);
});
