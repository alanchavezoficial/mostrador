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

test('cart page screenshot', async ({ page }) => {
  await page.goto(`${BASE}/cart`);
  await page.setViewportSize({ width: 1280, height: 900 });
  await page.screenshot({ path: 'screenshots/cart.png', fullPage: true });
});

test('orders history screenshot', async ({ page }) => {
  await page.goto(`${BASE}/orders/history`);
  await page.setViewportSize({ width: 1280, height: 900 });
  await page.screenshot({ path: 'screenshots/orders-history.png', fullPage: true });
});

test('wishlist screenshot', async ({ page }) => {
  await page.goto(`${BASE}/wishlist`);
  await page.setViewportSize({ width: 1280, height: 900 });
  await page.screenshot({ path: 'screenshots/wishlist.png', fullPage: true });
});