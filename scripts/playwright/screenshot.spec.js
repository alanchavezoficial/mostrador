const { test } = require('@playwright/test');

const BASE = process.env.BASE_URL || 'http://localhost/mostrador';

test('desktop admin menu', async ({ page }) => {
  await page.goto(`${BASE}/admin/dashboard`);
  // Open menu on mobile-sized viewport
  await page.setViewportSize({ width: 1280, height: 800 });
  await page.screenshot({ path: 'screenshots/admin-desktop.png', fullPage: true });
});

test('mobile admin menu open', async ({ page }) => {
  await page.goto(`${BASE}/admin/dashboard`);
  await page.setViewportSize({ width: 375, height: 800 });
  await page.click('#menu-toggle');
  await page.waitForTimeout(250);
  await page.screenshot({ path: 'screenshots/admin-mobile-open.png', fullPage: true });
});