import { chromium } from '@playwright/test';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';

const root = process.cwd();
const baseURL = process.env.E2E_BASE_URL ?? 'http://localhost';
const proofImage = path.join(root, 'docs', 'assets', 'proof-page.png');
const socialImage = path.join(root, 'public', 'images', 'rescue-lab-social.png');

await mkdir(path.dirname(proofImage), { recursive: true });
await mkdir(path.dirname(socialImage), { recursive: true });

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

try {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto(`${baseURL}/proof`, { waitUntil: 'networkidle' });
    await page.screenshot({ path: proofImage });

    await page.setViewportSize({ width: 1280, height: 640 });
    await page.goto(`${baseURL}/images/rescue-lab-social.svg`, { waitUntil: 'networkidle' });
    await page.screenshot({ path: socialImage });
} finally {
    await browser.close();
}

console.log(`Proof screenshot: ${proofImage}`);
console.log(`Social preview: ${socialImage}`);
