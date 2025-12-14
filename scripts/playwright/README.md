Playwright visual tests

Install dev deps (from repository root):

```bash
npm init -y
npm i -D @playwright/test
npx playwright install
```

Run tests:

```bash
npx playwright test scripts/playwright/screenshot.spec.js
```

Set `BASE_URL` env var if your site is served from a different path.
