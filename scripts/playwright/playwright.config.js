// Simple Playwright config for screenshots
module.exports = {
  timeout: 30000,
  use: {
    headless: true,
    viewport: { width: 1280, height: 800 }
  }
};