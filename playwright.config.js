import { defineConfig, devices } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';
import 'dotenv/config';

const rootDir = path.dirname(fileURLToPath(import.meta.url));

/**
 * @see https://playwright.dev/docs/test-configuration
 * 环境变量：
 * - PLAYWRIGHT_BASE_URL：应用根地址，默认 http://127.0.0.1:9000（避开 Windows 常见保留端口段）
 * - PLAYWRIGHT_SKIP_WEBSERVER=1：不自动执行 php artisan serve（用 Laragon 等已有站点时）
 * - PLAYWRIGHT_SKIP_DB_PREP=1：跳过 global-setup 中的 migrate + AdminSeeder（自行保证库可用时）
 * - PLAYWRIGHT_SLOW_MO：操作间隔毫秒数，默认 0
 */
export default defineConfig({
    testDir: './e2e',
    globalSetup: path.join(rootDir, 'e2e', 'global-setup.mjs'),
    outputDir: 'e2e/output/test-results',
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : 1,
    reporter: [
        ['list'],
        ['html', { outputFolder: 'e2e/output/html-report', open: 'never' }],
    ],
    use: {
        baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:9000',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        headless: process.env.PLAYWRIGHT_HEADLESS === '1',
        slowMo:
            process.env.PLAYWRIGHT_SLOW_MO !== undefined
                ? Number(process.env.PLAYWRIGHT_SLOW_MO)
                : 0,
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
    webServer: process.env.PLAYWRIGHT_SKIP_WEBSERVER
        ? undefined
        : {
              command: 'php artisan serve --host=127.0.0.1 --port=9000',
              url: 'http://127.0.0.1:9000',
              reuseExistingServer: !process.env.CI,
              timeout: 120_000,
          },
});
