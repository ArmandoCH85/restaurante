import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const ADMIN_URL = 'http://restaurante.test/admin';
const MODULE_URL = 'http://restaurante.test/admin/operaciones-caja';
const ACCESS_CODE = process.env.CASH_REGISTER_PIN ?? '010132';
const OUT_DIR = path.resolve('storage/app/ux-audit/cash-register');

const stepTimes = [];

function nowMs() {
    return Date.now();
}

function normalizeText(value) {
    return String(value ?? '').replace(/\s+/g, ' ').trim();
}

async function startStep(name) {
    const startedAt = nowMs();
    return {
        name,
        end: (status, notes = '') => {
            stepTimes.push({
                name,
                status,
                notes,
                durationMs: nowMs() - startedAt,
            });
        },
    };
}

async function ensureDir() {
    await fs.mkdir(OUT_DIR, { recursive: true });
}

async function captureInteractive(page, slug) {
    const data = await page.evaluate(() => {
        const isVisible = (el) => {
            const style = window.getComputedStyle(el);
            const rect = el.getBoundingClientRect();

            return (
                style.visibility !== 'hidden' &&
                style.display !== 'none' &&
                rect.width > 0 &&
                rect.height > 0
            );
        };

        const nodes = Array.from(
            document.querySelectorAll(
                'a,button,input,select,textarea,[role="button"],[tabindex]:not([tabindex="-1"])'
            )
        )
            .filter((el) => isVisible(el))
            .slice(0, 200)
            .map((el, index) => {
                const tag = el.tagName.toLowerCase();
                const id = el.id || null;
                const name = el.getAttribute('name');
                const type = el.getAttribute('type');
                const role = el.getAttribute('role');
                const ariaLabel = el.getAttribute('aria-label');
                const placeholder = el.getAttribute('placeholder');
                const text = (el.innerText || el.textContent || '').replace(/\s+/g, ' ').trim();

                return {
                    index: index + 1,
                    tag,
                    id,
                    name,
                    type,
                    role,
                    ariaLabel,
                    placeholder,
                    text,
                };
            });

        return {
            url: window.location.href,
            title: document.title,
            count: nodes.length,
            elements: nodes,
        };
    });

    const jsonPath = path.join(OUT_DIR, `${slug}-interactive.json`);
    const txtPath = path.join(OUT_DIR, `${slug}-interactive.txt`);

    const textLines = [
        `URL: ${data.url}`,
        `Title: ${data.title}`,
        `Interactive count: ${data.count}`,
        '',
        ...data.elements.map((el) =>
            [
                `${el.index}. <${el.tag}>`,
                el.id ? `id=${el.id}` : null,
                el.name ? `name=${el.name}` : null,
                el.type ? `type=${el.type}` : null,
                el.role ? `role=${el.role}` : null,
                el.ariaLabel ? `aria-label=${el.ariaLabel}` : null,
                el.placeholder ? `placeholder=${el.placeholder}` : null,
                el.text ? `text="${el.text}"` : null,
            ]
                .filter(Boolean)
                .join(' | ')
        ),
    ].join('\n');

    await fs.writeFile(jsonPath, JSON.stringify(data, null, 2), 'utf8');
    await fs.writeFile(txtPath, textLines, 'utf8');

    return { jsonPath, txtPath, count: data.count, url: data.url };
}

async function captureScreen(page, slug) {
    const shotPath = path.join(OUT_DIR, `${slug}.png`);
    await page.screenshot({ path: shotPath, fullPage: true });
    return shotPath;
}

async function clickByText(page, texts, options = {}) {
    for (const text of texts) {
        const buttonLocator = page.getByRole('button', { name: new RegExp(text, 'i') }).first();
        if (await buttonLocator.count()) {
            try {
                await buttonLocator.click(options);
            } catch {
                await buttonLocator.click({ ...options, force: true });
            }
            return text;
        }

        const linkLocator = page.getByRole('link', { name: new RegExp(text, 'i') }).first();

        if (await linkLocator.count()) {
            try {
                await linkLocator.click(options);
            } catch {
                await linkLocator.click({ ...options, force: true });
            }
            return text;
        }
    }

    return null;
}

async function fillFirstVisible(page, selectors, value) {
    for (const sel of selectors) {
        const locator = page.locator(sel).first();

        if (await locator.count()) {
            if (await locator.isVisible()) {
                await locator.fill(value);
                return sel;
            }
        }
    }

    return null;
}

async function safeGoto(page, url, retries = 3) {
    let lastError;

    for (let i = 0; i < retries; i += 1) {
        try {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            await page.waitForLoadState('networkidle');
            return;
        } catch (error) {
            lastError = error;
            await page.waitForTimeout(700);
        }
    }

    throw lastError;
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

    const evidence = [];
    const confusionPoints = [];

    const step1 = await startStep('Abrir login admin');
    await safeGoto(page, ADMIN_URL);
    evidence.push({
        step: 'login-screen',
        screenshot: await captureScreen(page, '01-login'),
        interactive: await captureInteractive(page, '01-login'),
    });
    step1.end('ok');

    const step2 = await startStep('Ingresar con codigo PIN');
    const pinSelector = await fillFirstVisible(
        page,
        [
            'input#data\\.code',
            'input[id="data.code"]',
            'input[name="code"]',
            'input[name="pin"]',
            'input[type="number"]',
            'input[placeholder*="Código"]',
            'input[placeholder*="codigo"]',
            'input[type="password"]',
            'input[type="text"]',
        ],
        ACCESS_CODE
    );

    if (!pinSelector) {
        throw new Error('No se encontro campo para codigo de acceso.');
    }

    const loginButton = await clickByText(page, ['Ingresar', 'Entrar', 'Login']);

    if (!loginButton) {
        throw new Error('No se encontro boton de ingreso.');
    }

    await Promise.race([
        page.waitForURL(/\/admin(\/)?$/, { timeout: 15000 }),
        page.waitForLoadState('networkidle'),
    ]);

    evidence.push({
        step: 'after-login',
        screenshot: await captureScreen(page, '02-after-login'),
        interactive: await captureInteractive(page, '02-after-login'),
    });
    step2.end('ok', `Campo: ${pinSelector}; Boton: ${loginButton}`);

    const step3 = await startStep('Abrir modulo operaciones caja');
    await safeGoto(page, MODULE_URL);
    evidence.push({
        step: 'cash-register-list',
        screenshot: await captureScreen(page, '03-list'),
        interactive: await captureInteractive(page, '03-list'),
    });
    step3.end('ok');

    const step4 = await startStep('Abrir flujo editar/cerrar caja');
    const closeButton = await clickByText(page, ['Cerrar', 'Editar', 'Modificar']);

    if (!closeButton) {
        confusionPoints.push('No se encontro accion de cierre/edicion visible en tabla.');
        step4.end('warning', 'Boton de editar/cerrar no detectado');
    } else {
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(800);
        evidence.push({
            step: '04-edit-close-form',
            screenshot: await captureScreen(page, '04-edit-close-form'),
            interactive: await captureInteractive(page, '04-edit-close-form'),
        });
        step4.end('ok', `Boton usado: ${closeButton}`);
    }

    const step5 = await startStep('Guardar cierre caja y volver al listado');
    await fillFirstVisible(
        page,
        [
            'input#mountedTableActionsData\\.0\\.manual_cash',
            'input[id="mountedTableActionsData.0.manual_cash"]',
            'input#data\\.manual_cash',
            'input[id="data.manual_cash"]',
        ],
        '294.50'
    );
    await fillFirstVisible(
        page,
        [
            'input#mountedTableActionsData\\.0\\.manual_yape',
            'input[id="mountedTableActionsData.0.manual_yape"]',
            'input#data\\.manual_yape',
            'input[id="data.manual_yape"]',
        ],
        '0'
    );
    await fillFirstVisible(
        page,
        [
            'input#mountedTableActionsData\\.0\\.manual_plin',
            'input[id="mountedTableActionsData.0.manual_plin"]',
            'input#data\\.manual_plin',
            'input[id="data.manual_plin"]',
        ],
        '0'
    );
    await fillFirstVisible(
        page,
        [
            'input#mountedTableActionsData\\.0\\.manual_card',
            'input[id="mountedTableActionsData.0.manual_card"]',
            'input#data\\.manual_card',
            'input[id="data.manual_card"]',
        ],
        '0'
    );
    await fillFirstVisible(
        page,
        [
            'input#mountedTableActionsData\\.0\\.manual_didi',
            'input[id="mountedTableActionsData.0.manual_didi"]',
            'input#data\\.manual_didi',
            'input[id="data.manual_didi"]',
        ],
        '0'
    );
    await fillFirstVisible(
        page,
        [
            'input#mountedTableActionsData\\.0\\.manual_pedidos_ya',
            'input[id="mountedTableActionsData.0.manual_pedidos_ya"]',
            'input#data\\.manual_pedidos_ya',
            'input[id="data.manual_pedidos_ya"]',
        ],
        '0'
    );
    await fillFirstVisible(
        page,
        [
            'input#mountedTableActionsData\\.0\\.manual_bita_express',
            'input[id="mountedTableActionsData.0.manual_bita_express"]',
            'input#data\\.manual_bita_express',
            'input[id="data.manual_bita_express"]',
        ],
        '0'
    );

    await fillFirstVisible(
        page,
        [
            'textarea[name*="closing_observations"]',
            'textarea[id*="closing_observations"]',
            'textarea[name*="observation"]',
        ],
        'Cierre automatizado para auditoria UX.'
    );

    const saveClose = await clickByText(page, ['Cerrar Caja', 'Guardar', 'Actualizar']);
    if (saveClose) {
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1200);
    }

    await safeGoto(page, MODULE_URL);

    evidence.push({
        step: '05-list-after-close',
        screenshot: await captureScreen(page, '05-list-after-close'),
        interactive: await captureInteractive(page, '05-list-after-close'),
    });

    step5.end(saveClose ? 'ok' : 'warning', saveClose ? `Boton: ${saveClose}` : 'No se encontro boton de guardado en cierre');

    const step6 = await startStep('Abrir formulario crear caja');
    if (await page.locator('#livewire-error').count()) {
        confusionPoints.push('Aparecio overlay #livewire-error bloqueando interacciones despues del cierre.');
    }
    const createButton = await clickByText(page, ['Abrir Nueva Caja', 'Abrir caja', 'Crear', 'Nuevo']);

    if (!createButton) {
        confusionPoints.push('No se encontro CTA claro para crear caja en el listado.');
        step6.end('warning', 'CTA de crear no encontrado');
    } else {
        const urlBeforeCreate = page.url();
        await page.waitForTimeout(600);

        if (page.url() === urlBeforeCreate) {
            await safeGoto(page, `${MODULE_URL}/create`);
        }

        evidence.push({
            step: '06-create-form',
            screenshot: await captureScreen(page, '06-create-form'),
            interactive: await captureInteractive(page, '06-create-form'),
        });
        step6.end('ok', `Boton usado: ${createButton}`);
    }

    const step7 = await startStep('Guardar apertura caja');
    const openingField = await fillFirstVisible(
        page,
        [
            'input#data\\.opening_amount',
            'input[id="data.opening_amount"]',
            'input[name*="opening_amount"]',
            'input[id*="opening_amount"]',
            'input[name*="opening"]',
            'input[id*="opening"]',
            'input[name*="monto"]',
        ],
        '150'
    );

    if (!openingField) {
        confusionPoints.push('Formulario create: no se encontro input de monto inicial con selector claro.');
    }

    await fillFirstVisible(
        page,
        ['textarea[name*="observation"]', 'textarea[name*="notes"]', 'textarea'],
        'Apertura automatizada para auditoria UX.'
    );

    const saveCreate = await clickByText(page, ['Abrir Caja', 'Guardar', 'Crear']);
    if (saveCreate) {
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);
    }

    evidence.push({
        step: '07-after-create-save',
        screenshot: await captureScreen(page, '07-after-create-save'),
        interactive: await captureInteractive(page, '07-after-create-save'),
    });

    if (!saveCreate) {
        step7.end('warning', 'No se encontro boton de guardado en create');
    } else {
        step7.end('ok', `Campo monto: ${openingField ?? 'no-detectado'}; Boton: ${saveCreate}`);
    }

    if (!page.url().includes('/operaciones-caja')) {
        confusionPoints.push('Despues de abrir caja el flujo sale del modulo (redirect), se pierde continuidad de la tarea.');
        const stepBack = await startStep('Volver al listado de caja');
        await safeGoto(page, MODULE_URL);
        evidence.push({
            step: 'list-after-return',
            screenshot: await captureScreen(page, '08-list-return'),
            interactive: await captureInteractive(page, '08-list-return'),
        });
        stepBack.end('ok');
    }

    evidence.push({
        step: 'final-list',
        screenshot: await captureScreen(page, '09-final-list'),
        interactive: await captureInteractive(page, '09-final-list'),
    });

    const report = {
        generatedAt: new Date().toISOString(),
        urls: {
            admin: ADMIN_URL,
            module: MODULE_URL,
            final: page.url(),
        },
        stepTimes,
        confusionPoints,
        evidence,
    };

    const reportPath = path.join(OUT_DIR, 'ux-flow-report.json');
    await fs.writeFile(reportPath, JSON.stringify(report, null, 2), 'utf8');

    const mdPath = path.join(OUT_DIR, 'ux-flow-report.md');
    const md = [
        '# UX Flow Report - Cash Register',
        '',
        `- Generated: ${report.generatedAt}`,
        `- Final URL: ${report.urls.final}`,
        '',
        '## Step Times',
        ...stepTimes.map((s, i) => `- ${i + 1}. ${s.name} | ${s.status} | ${s.durationMs}ms | ${s.notes || '-'}`),
        '',
        '## Confusion Points',
        ...(confusionPoints.length ? confusionPoints.map((p) => `- ${p}`) : ['- None detected by automation']),
        '',
        '## Evidence Files',
        ...evidence.map((e) => `- ${e.step}: ${e.screenshot}`),
    ].join('\n');
    await fs.writeFile(mdPath, md, 'utf8');

    await context.close();
    await browser.close();

    // eslint-disable-next-line no-console
    console.log(JSON.stringify({ ok: true, reportPath, mdPath, outDir: OUT_DIR }, null, 2));
}

run().catch(async (error) => {
    const failurePath = path.join(OUT_DIR, 'ux-flow-error.log');
    await fs.mkdir(OUT_DIR, { recursive: true });
    await fs.writeFile(failurePath, String(error?.stack || error), 'utf8');
    // eslint-disable-next-line no-console
    console.error(error);
    process.exit(1);
});
