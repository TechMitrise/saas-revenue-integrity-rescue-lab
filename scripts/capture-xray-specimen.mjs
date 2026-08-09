import { chromium } from '@playwright/test';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';

const root = process.cwd();
const baseURL = process.env.E2E_BASE_URL ?? 'http://localhost';
async function envValue(key) {
    if (process.env[key]) return process.env[key];

    try {
        const env = await readFile(path.join(root, '.env'), 'utf8');
        const line = env.split(/\r?\n/).find((candidate) => candidate.startsWith(`${key}=`));
        if (! line) return null;

        return line.slice(key.length + 1).replace(/^["']|["']$/g, '');
    } catch {
        return null;
    }
}

const accessCode = await envValue('CRITICAL_XRAY_ACCESS_CODE');
const output = path.join(root, 'docs', 'xray-specimen');
const screenshots = path.join(output, 'screenshots');
const route = '/critical-software-xray/sample/revenue-access-failure';
const pdfPath = path.join(output, 'critical-software-xray-specimen.pdf');
const printPageShots = process.env.XRAY_PRINT_PAGE_SHOTS_DIR;

if (! accessCode) {
    throw new Error('CRITICAL_XRAY_ACCESS_CODE is required to capture the protected X-Ray specimen.');
}

await mkdir(screenshots, { recursive: true });

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
const page = await context.newPage();

async function unlock() {
    await page.goto(`${baseURL}${route}/access`, { waitUntil: 'networkidle' });
    await page.getByLabel('Access code').fill(accessCode);
    await page.getByRole('button', { name: 'Unlock specimen' }).click();
    await page.waitForURL(new RegExp(`${route}$`));
    await page.waitForLoadState('networkidle');
}

async function capture(name, hash, selector = 'body', before = null) {
    await page.goto(`${baseURL}${route}${hash}`, { waitUntil: 'networkidle' });
    await page.locator(selector).first().scrollIntoViewIfNeeded();
    if (before) await before();
    await page.waitForTimeout(250);
    await page.screenshot({ path: path.join(screenshots, name), fullPage: false, animations: 'disabled' });
}

async function stampPdfAuthor(file) {
    const pdf = await readFile(file);
    const target = Buffer.from('/Producer (Skia/PDF m151)');
    const replacement = Buffer.from('/Author (Tech Mitrise)   ');

    if (target.length !== replacement.length) {
        throw new Error('PDF metadata replacement must preserve byte length.');
    }

    const index = pdf.indexOf(target);
    if (index === -1) {
        throw new Error('Unable to find Chromium producer metadata for author stamping.');
    }

    replacement.copy(pdf, index);
    await writeFile(file, pdf);
}

async function validatePdf(file) {
    const pdf = await readFile(file);
    const pageCount = (pdf.toString('latin1').match(/\/Type\s*\/Page\b/g) ?? []).length;

    if (pageCount < 12 || pageCount > 16) {
        throw new Error(`Expected a purposeful 12-16 page X-Ray PDF; generated ${pageCount} pages.`);
    }

    const metadata = pdf.toString('latin1', 0, Math.min(pdf.length, 1600));
    for (const required of ['Critical Software X-Ray Specimen', '/Author (Tech Mitrise)']) {
        if (! metadata.includes(required)) {
            throw new Error(`Generated PDF metadata is missing: ${required}`);
        }
    }

    return pageCount;
}

try {
    await unlock();
    await page.emulateMedia({ reducedMotion: 'reduce' });

    await capture('01-executive-risk-brief.png', '#executive-brief', '#executive-brief');
    await capture('02-critical-path-divergence.png', '#critical-path', '#critical-path', async () => {
        await page.locator('[data-stage="queue"]').click();
    });
    await capture('03-expected-vs-actual-state.png', '#state-matrix', '#state-matrix');
    await capture('04-recovery-rollback-decision.png', '#recovery', '#recovery');
    await capture('05-priced-30-day-roadmap.png', '#roadmap', '#roadmap');

    await page.goto(`${baseURL}${route}`, { waitUntil: 'networkidle' });
    await page.emulateMedia({ media: 'print', reducedMotion: 'reduce' });
    const printText = await page.locator('.xray-print-document').evaluate((node) => node.textContent ?? '');
    for (const required of [
        'Synthetic professional-service specimen',
        'Executive Risk Brief',
        'Critical-Path Map',
        '$2,250',
        '$9,500',
        'Request a 20-minute Critical Software Risk Triage',
    ]) {
        if (! printText.includes(required)) {
            throw new Error(`Printable X-Ray specimen is missing required text: ${required}`);
        }
    }
    if (printPageShots) {
        await mkdir(printPageShots, { recursive: true });
        const pages = await page.locator('.print-page').count();
        for (let index = 0; index < pages; index += 1) {
            await page.locator('.print-page').nth(index).screenshot({
                path: path.join(printPageShots, `${String(index + 1).padStart(2, '0')}.png`),
                animations: 'disabled',
            });
        }
    }
    await page.pdf({
        path: pdfPath,
        format: 'Letter',
        printBackground: true,
        margin: { top: '0.35in', right: '0.35in', bottom: '0.35in', left: '0.35in' },
    });
    await stampPdfAuthor(pdfPath);
    const pageCount = await validatePdf(pdfPath);
    console.log(`X-Ray specimen PDF pages: ${pageCount}`);
} finally {
    await context.close();
    await browser.close();
}

console.log(`X-Ray specimen screenshots: ${screenshots}`);
console.log(`X-Ray specimen PDF: ${pdfPath}`);
