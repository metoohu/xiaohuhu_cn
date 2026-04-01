---
name: e2e-playwright
description: 在本 Laravel 项目中运行与编写 Playwright E2E 测试（e2e 目录、playwright.config.js、npm run test:e2e）。在用户要端到端测试、改 e2e 用例或 CI 集成 Playwright 时使用。
---

# Playwright E2E（本项目）

## 命令（PowerShell 建议 npx.cmd）

| 命令 | 说明 |
|------|------|
| `npm.cmd run test:e2e` | 跑全部 e2e |
| `npm.cmd run test:e2e:ui` | UI 模式调试 |
| `npm.cmd run test:e2e:headed` | 有头浏览器 |
| `npm.cmd run test:e2e:report` | 打开上次 HTML 报告 |
| `npx.cmd playwright install` | 安装/更新浏览器内核 |

## 配置

- `playwright.config.js`：`baseURL` 默认 `http://127.0.0.1:8000`，可用环境变量 `PLAYWRIGHT_BASE_URL` 覆盖（与 Laragon 虚拟主机一致时改成如 `http://laravel.test`）。
- 默认会启动 `php artisan serve`（`webServer`）。若站点已由 Laragon 提供，设 **`PLAYWRIGHT_SKIP_WEBSERVER=1`** 再跑测试。
- 测试文件：`e2e/*.spec.js`；报告与失败截图在 `e2e/output/`（已 gitignore）。

## 编写用例

- 使用 `import { test, expect } from '@playwright/test'`。
- 使用相对路径：`await page.goto('/')` 会拼接 `baseURL`。

## 首次或 CI

- 执行 `npx.cmd playwright install`（或 CI 用 `npx playwright install --with-deps` 于 Linux）。
