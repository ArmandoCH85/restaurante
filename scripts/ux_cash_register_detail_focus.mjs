import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const LOGIN_URL = 'http://restaurante.test/admin/login';
const LIST_URL = 'http://restaurante.test/admin/operaciones-caja';
const PIN = process.env.CASH_REGISTER_PIN ?? '010132';
const OUT_DIR = path.resolve('storage/app/ux-audit/cash-register-detail-focus');

const steps = [];
const evidence = [];

const t0 = () => Date.now();

function stepTimer(name) {
  const started = t0();
  return {
    done(status = 'ok', notes = '') {
      steps.push({ name, status, notes, durationMs: t0() - started });
    },
  };
}

async function ensureDir() {
  await fs.mkdir(OUT_DIR, { recursive: true });
}

async function gotoStable(page, url) {
  let last;
  for (let i = 0; i < 3; i += 1) {
    try {
      await page.goto(url, { waitUntil: 'domcontentloaded' });
      await page.waitForLoadState('networkidle');
      return;
    } catch (error) {
      last = error;
      await page.waitForTimeout(700);
    }
  }
  throw last;
}

async function interactiveSnapshot(page, slug) {
  const data = await page.evaluate(() => {
    const visible = (el) => {
      const style = window.getComputedStyle(el);
      const r = el.getBoundingClientRect();
      return style.display !== 'none' && style.visibility !== 'hidden' && r.width > 0 && r.height > 0;
    };

    const rows = Array.from(document.querySelectorAll('a,button,input,select,textarea,[role="button"],[tabindex]:not([tabindex="-1"])'))
      .filter((el) => visible(el))
      .slice(0, 250)
      .map((el, idx) => ({
        idx: idx + 1,
        tag: el.tagName.toLowerCase(),
        id: el.id || null,
        name: el.getAttribute('name'),
        type: el.getAttribute('type'),
        aria: el.getAttribute('aria-label'),
        text: (el.innerText || el.textContent || '').replace(/\s+/g, ' ').trim(),
      }));

    return { url: location.href, title: document.title, count: rows.length, rows };
  });

  const txt = [
    `URL: ${data.url}`,
    `Title: ${data.title}`,
    `Interactive count: ${data.count}`,
    '',
    ...data.rows.map((r) => [
      `${r.idx}. <${r.tag}>`,
      r.id ? `id=${r.id}` : null,
      r.name ? `name=${r.name}` : null,
      r.type ? `type=${r.type}` : null,
      r.aria ? `aria-label=${r.aria}` : null,
      r.text ? `text="${r.text}"` : null,
    ].filter(Boolean).join(' | ')),
  ].join('\n');

  const txtPath = path.join(OUT_DIR, `${slug}-interactive.txt`);
  const jsonPath = path.join(OUT_DIR, `${slug}-interactive.json`);
  await fs.writeFile(txtPath, `${txt}\n`, 'utf8');
  await fs.writeFile(jsonPath, JSON.stringify(data, null, 2), 'utf8');

  return { txtPath, jsonPath };
}

async function capture(page, slug, label) {
  const shotPath = path.join(OUT_DIR, `${slug}.png`);
  await page.screenshot({ path: shotPath, fullPage: true });
  const snap = await interactiveSnapshot(page, slug);
  evidence.push({ step: label, screenshot: shotPath, interactive: snap.txtPath, url: page.url() });
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

  const s1 = stepTimer('Login admin con PIN');
  await gotoStable(page, LOGIN_URL);
  await page.locator('input#data\\.code, input[id="data.code"], input[type="number"]').first().fill(PIN);
  await page.getByRole('button', { name: /Ingresar/i }).first().click({ force: true });
  await page.waitForLoadState('networkidle');
  await capture(page, '01-after-login', 'after-login');
  s1.done('ok');

  const s2 = stepTimer('Abrir listado de caja');
  await gotoStable(page, LIST_URL);
  await capture(page, '02-list', 'list');
  s2.done('ok');

  const s3 = stepTimer('Entrar al detalle con accion Ver');
  const firstView = page.getByRole('link', { name: /^Ver$/i }).first();
  if (!await firstView.count()) {
    s3.done('warning', 'No se encontro enlace Ver');
  } else {
    await firstView.click({ force: true });
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1200);
    await capture(page, '03-detail', 'detail');
    s3.done('ok', page.url());
  }

  const s4 = stepTimer('Abrir accion Cerrar Caja desde detalle');
  const closeBtn = page.getByRole('button', { name: /Cerrar Caja/i }).first();
  if (!await closeBtn.count()) {
    s4.done('warning', 'No se encontro boton Cerrar Caja');
  } else {
    await closeBtn.click({ force: true });
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await capture(page, '04-close-action-open', 'close-action-open');
    s4.done('ok', page.url());
  }

  const report = {
    generatedAt: new Date().toISOString(),
    steps,
    evidence,
  };

  await fs.writeFile(path.join(OUT_DIR, 'report.json'), JSON.stringify(report, null, 2), 'utf8');
  const md = [
    '# Cash Register Detail Focus Report',
    '',
    ...steps.map((s, i) => `- ${i + 1}. ${s.name} | ${s.status} | ${s.durationMs}ms | ${s.notes || '-'}`),
    '',
    '## Evidence',
    ...evidence.map((e) => `- ${e.step}: ${e.screenshot}`),
  ].join('\n');
  await fs.writeFile(path.join(OUT_DIR, 'report.md'), `${md}\n`, 'utf8');

  await context.close();
  await browser.close();

  // eslint-disable-next-line no-console
  console.log(JSON.stringify({ ok: true, outDir: OUT_DIR }, null, 2));
}

run().catch(async (error) => {
  await ensureDir();
  await fs.writeFile(path.join(OUT_DIR, 'error.log'), String(error?.stack || error), 'utf8');
  process.exit(1);
});
