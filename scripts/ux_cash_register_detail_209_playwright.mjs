import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const ADMIN_LOGIN_URL = 'http://restaurante.test/admin/login';
const LIST_URL = 'http://restaurante.test/admin/operaciones-caja';
const DETAIL_URL = 'http://restaurante.test/admin/operaciones-caja/209';
const CREATE_URL = 'http://restaurante.test/admin/operaciones-caja/create';
const ACCESS_CODE = process.env.CASH_REGISTER_PIN ?? '010132';
const OUT_DIR = path.resolve('storage/app/ux-audit/cash-register-209');

const timings = [];
const issues = [];
const evidence = [];

const now = () => Date.now();

function trackStep(name) {
    const started = now();

    return {
        done(status = 'ok', notes = '') {
            timings.push({ name, status, notes, durationMs: now() - started });
        },
    };
}

async function ensureDir() {
    await fs.mkdir(OUT_DIR, { recursive: true });
}

async function screenshot(page, slug) {
    const file = path.join(OUT_DIR, `${slug}.png`);
    await page.screenshot({ path: file, fullPage: true });
    return file;
}

async function snapshotInteractive(page, slug) {
    const data = await page.evaluate(() => {
        const isVisible = (el) => {
            const style = window.getComputedStyle(el);
            const rect = el.getBoundingClientRect();

            return style.visibility !== 'hidden' && style.display !== 'none' && rect.width > 0 && rect.height > 0;
        };

        const elements = Array.from(document.querySelectorAll('a,button,input,select,textarea,[role="button"],[tabindex]:not([tabindex="-1"])'))
            .filter((el) => isVisible(el))
            .slice(0, 250)
            .map((el, index) => ({
                index: index + 1,
                tag: el.tagName.toLowerCase(),
                id: el.id || null,
                type: el.getAttribute('type'),
                name: el.getAttribute('name'),
                aria: el.getAttribute('aria-label'),
                text: (el.innerText || el.textContent || '').replace(/\s+/g, ' ').trim(),
            }));

        return {
            url: window.location.href,
            title: document.title,
            count: elements.length,
            elements,
        };
    });

    const jsonPath = path.join(OUT_DIR, `${slug}-interactive.json`);
    const txtPath = path.join(OUT_DIR, `${slug}-interactive.txt`);

    await fs.writeFile(jsonPath, JSON.stringify(data, null, 2), 'utf8');

    const lines = [
        `URL: ${data.url}`,
        `Title: ${data.title}`,
        `Interactive count: ${data.count}`,
        '',
        ...data.elements.map((item) => [
            `${item.index}. <${item.tag}>`,
            item.id ? `id=${item.id}` : null,
            item.type ? `type=${item.type}` : null,
            item.name ? `name=${item.name}` : null,
            item.aria ? `aria-label=${item.aria}` : null,
            item.text ? `text="${item.text}"` : null,
        ].filter(Boolean).join(' | ')),
    ];

    await fs.writeFile(txtPath, `${lines.join('\n')}\n`, 'utf8');

    return { jsonPath, txtPath };
}

async function capture(page, slug, stepName) {
    const shot = await screenshot(page, slug);
    const snap = await snapshotInteractive(page, slug);
    evidence.push({ step: stepName, screenshot: shot, interactive: snap.txtPath, url: page.url() });
}

async function gotoStable(page, url) {
    let lastError;

    for (let attempt = 0; attempt < 3; attempt += 1) {
        try {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            await page.waitForLoadState('networkidle');
            return;
        } catch (error) {
            lastError = error;
            await page.waitForTimeout(800);

            if (String(error?.message || '').includes('ERR_ABORTED')) {
                try {
                    await page.waitForLoadState('networkidle', { timeout: 6000 });
                    return;
                } catch {
                    // continue retry loop
                }
            }
        }
    }

    throw lastError;
}

async function clickByLabel(page, labels) {
    for (const label of labels) {
        const button = page.getByRole('button', { name: new RegExp(label, 'i') }).first();
        if (await button.count()) {
            await button.click({ force: true });
            return `button:${label}`;
        }

        const link = page.getByRole('link', { name: new RegExp(label, 'i') }).first();
        if (await link.count()) {
            await link.click({ force: true });
            return `link:${label}`;
        }
    }

    return null;
}

async function fillFirst(page, selectors, value) {
    for (const selector of selectors) {
        const el = page.locator(selector).first();
        if (await el.count()) {
            await el.fill(value);
            return selector;
        }
    }

    return null;
}

async function run() {
    await ensureDir();

    let browser;
    try {
        browser = await chromium.launch({ headless: true, channel: 'msedge' });
    } catch {
        browser = await chromium.launch({ headless: true });
    }

    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();
    page.setDefaultTimeout(30000);

    const step1 = trackStep('Abrir login y autenticar');
    await gotoStable(page, ADMIN_LOGIN_URL);
    await capture(page, '01-login', 'login');

    const pinField = await fillFirst(page, [
        'input#data\\.code',
        'input[id="data.code"]',
        'input[type="number"]',
        'input[name="code"]',
    ], ACCESS_CODE);

    const loginAction = await clickByLabel(page, ['Ingresar', 'Entrar', 'Login']);
    await page.waitForLoadState('networkidle');
    await capture(page, '02-after-login', 'after-login');
    step1.done('ok', `PIN: ${pinField ?? 'no detectado'} | Acción: ${loginAction ?? 'no detectada'}`);

    const step2 = trackStep('Abrir detalle caja 209');
    await gotoStable(page, DETAIL_URL);
    await capture(page, '03-detail-209', 'detail-209');
    step2.done('ok');

    const step3 = trackStep('Intentar cerrar/editar desde detalle');
    const closeFromDetail = await clickByLabel(page, ['Cerrar Caja', 'Cerrar', 'Editar']);

    if (!closeFromDetail) {
        issues.push('En detalle 209 no se encontró CTA visible para cerrar/editar.');
        step3.done('warning', 'Sin CTA cerrar/editar');
    } else {
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(800);
        await capture(page, '04-edit-from-detail', 'edit-from-detail');

        await fillFirst(page, [
            'input#data\\.manual_cash',
            'input[id="data.manual_cash"]',
            'input#mountedTableActionsData\\.0\\.manual_cash',
            'input[id="mountedTableActionsData.0.manual_cash"]',
        ], '100');

        const saveAction = await clickByLabel(page, ['Guardar', 'Cerrar Caja', 'Actualizar']);
        if (saveAction) {
            await page.waitForLoadState('networkidle');
            await page.waitForTimeout(1200);
        } else {
            issues.push('No se encontró botón de guardado al editar/cerrar desde detalle.');
        }

        await capture(page, '05-after-edit-save', 'after-edit-save');
        step3.done(saveAction ? 'ok' : 'warning', `Acción save: ${saveAction ?? 'no detectada'}`);
    }

    const step4 = trackStep('Volver al listado de cajas');
    await gotoStable(page, LIST_URL);
    await capture(page, '06-list', 'list');
    step4.done('ok');

    const step5 = trackStep('Crear caja desde flujo estándar');
    let openCreate = await clickByLabel(page, ['Abrir Nueva Caja', 'Crear', 'Nuevo']);
    await page.waitForTimeout(700);

    if (page.url() === LIST_URL) {
        await gotoStable(page, CREATE_URL);
        openCreate = `${openCreate ?? 'no visible'} + fallback:/create`;
    }

    await capture(page, '07-create-page', 'create-page');

    const openingInput = await fillFirst(page, [
        'input#data\\.opening_amount',
        'input[id="data.opening_amount"]',
        'input[name*="opening_amount"]',
    ], '120');

    await fillFirst(page, [
        'textarea#data\\.observations',
        'textarea[id="data.observations"]',
        'textarea[name*="observation"]',
    ], 'Apertura para validación UX detalle caja 209.');

    const saveCreate = await clickByLabel(page, ['Abrir Nueva Caja', 'Crear', 'Guardar']);
    if (saveCreate) {
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1200);
    } else {
        issues.push('No se encontró acción de guardado en create.');
    }

    await capture(page, '08-after-create-save', 'after-create-save');
    step5.done(saveCreate ? 'ok' : 'warning', `Open: ${openCreate ?? 'no detectado'} | opening: ${openingInput ?? 'no detectado'} | save: ${saveCreate ?? 'no detectado'}`);

    const step6 = trackStep('Volver al listado final');
    await gotoStable(page, LIST_URL);
    await capture(page, '09-final-list', 'final-list');
    step6.done('ok');

    const report = {
        generatedAt: new Date().toISOString(),
        urls: { login: ADMIN_LOGIN_URL, detail: DETAIL_URL, list: LIST_URL, final: page.url() },
        timings,
        issues,
        evidence,
    };

    const jsonReport = path.join(OUT_DIR, 'ux-detail-209-report.json');
    const mdReport = path.join(OUT_DIR, 'ux-detail-209-report.md');

    await fs.writeFile(jsonReport, JSON.stringify(report, null, 2), 'utf8');

    const md = [
        '# UX Report - Cash Register Detail 209',
        '',
        `- Generated: ${report.generatedAt}`,
        `- Final URL: ${report.urls.final}`,
        '',
        '## Step Times',
        ...timings.map((t, i) => `- ${i + 1}. ${t.name} | ${t.status} | ${t.durationMs}ms | ${t.notes || '-'}`),
        '',
        '## Friction / Confusion',
        ...(issues.length ? issues.map((i) => `- ${i}`) : ['- Sin fricciones detectadas automáticamente']),
        '',
        '## Evidence',
        ...evidence.map((e) => `- ${e.step}: ${e.screenshot}`),
    ].join('\n');

    await fs.writeFile(mdReport, `${md}\n`, 'utf8');

    await context.close();
    await browser.close();

    // eslint-disable-next-line no-console
    console.log(JSON.stringify({ ok: true, outDir: OUT_DIR, jsonReport, mdReport }, null, 2));
}

run().catch(async (error) => {
    await ensureDir();
    await fs.writeFile(path.join(OUT_DIR, 'ux-detail-209-error.log'), String(error?.stack || error), 'utf8');
    // eslint-disable-next-line no-console
    console.error(error);
    process.exit(1);
});
