import { defineConfig } from '@playwright/test';

const appPort = 8001;
const casPort = 9800;
const appUrl = `http://127.0.0.1:${appPort}`;
const casUrl = `http://127.0.0.1:${casPort}/cas`;

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  retries: process.env.CI ? 2 : 0,
  timeout: 30_000,
  expect: {
    timeout: 5_000,
  },
  reporter: process.env.CI ? [['html'], ['list']] : 'list',
  use: {
    baseURL: appUrl,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  webServer: [
    {
      command: 'node dev/cas-stub/server.mjs',
      url: `${casUrl}/__health`,
      reuseExistingServer: !process.env.CI,
      env: {
        CAS_STUB_HOST: '127.0.0.1',
        CAS_STUB_PORT: String(casPort),
      },
    },
    {
      command: './scripts/e2e/serve-workbench.sh',
      url: `${appUrl}/`,
      reuseExistingServer: !process.env.CI,
      env: {
        APP_URL: appUrl,
        CAS_URL: casUrl,
        CAS_REDIRECT_LOGIN_ROUTE: 'dashboard',
        CAS_REDIRECT_LOGOUT_URL: `${appUrl}/`,
        DB_CONNECTION: 'sqlite',
        DB_DATABASE: 'vendor/orchestra/testbench-core/laravel/database/database.sqlite',
      },
    },
  ],
});
