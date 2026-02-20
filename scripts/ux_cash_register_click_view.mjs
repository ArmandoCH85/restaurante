import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const OUT_DIR = path.resolve('storage/app/ux-audit/cash-register-view-click');
const LOGIN_URL = 'http://restaurante.test/admin/login';
const LIST_URL = 'http://restaurante.test/admin/operaciones-caja';
const PIN = process.env.CASH_REGISTER_PIN ?? '010132';

async function ensureDir() {
  await fs.mkdir(OUT_DIR, { recursive: true });
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

  const gotoStable = async (url) => {
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
  };

  await gotoStable(LOGIN_URL);
  await page.locator('input#data\\.code, input[id="data.code"], input[type="number"]').first().fill(PIN);
  await page.getByRole('button', { name: /Ingresar/i }).first().click({ force: true });
  await page.waitForLoadState('networkidle');

  await gotoStable(LIST_URL);
  await page.screenshot({ path: path.join(OUT_DIR, '01-list.png'), fullPage: true });

  const firstView = page.getByRole('link', { name: /^Ver$/i }).first();
  const count = await firstView.count();

  if (!count) {
    await fs.writeFile(path.join(OUT_DIR, 'result.json'), JSON.stringify({ ok: false, reason: 'No se encontro link Ver en listado', url: page.url() }, null, 2));
    await browser.close();
    return;
  }

  await firstView.click({ force: true });
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(1200);

  await page.screenshot({ path: path.join(OUT_DIR, '02-after-click-ver.png'), fullPage: true });

  const data = {
    ok: true,
    finalUrl: page.url(),
    title: await page.title(),
  };

  await fs.writeFile(path.join(OUT_DIR, 'result.json'), JSON.stringify(data, null, 2));
  await browser.close();
}

run().catch(async (error) => {
  await ensureDir();
  await fs.writeFile(path.join(OUT_DIR, 'error.log'), String(error?.stack || error));
  process.exit(1);
});
