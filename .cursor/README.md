# Cursor 项目配置说明

## Rules（`.cursor/rules/*.mdc`）

| 文件 | 说明 |
|------|------|
| `laravel-core.mdc` | **始终应用**：双 guard、路由注册方式、`config/admin` & `front`、**与用户对话使用简体中文**（勿默认繁体） |
| `routing-middleware.mdc` | `routes/*`、`bootstrap/app.php`、中间件、路由顺序 |
| `admin-backend.mdc` | 后台控制器、Request、Blade、`admin.php` 模块对照 |
| `front-office.mdc` | 前台 `Front\*`、`front.*` 路由、`my` 与评论 |
| `php-conventions.mdc` | 全项目 PHP 风格与 Laravel 用法 |
| `database-models.mdc` | 迁移、模型、`AdminMenuItem` |
| `views-and-assets.mdc` | Blade、Vite/Tailwind、主题实现注意 |

在 Cursor **Settings → Rules** 中为项目启用上述规则；带 `globs` 的规则在编辑匹配文件时权重更高。

## Skills（`.cursor/skills/<name>/SKILL.md`）

| 目录 | 用途 |
|------|------|
| `admin-menu-sidebar` | 侧栏菜单表、模型、CRUD、侧栏模板与排错 |
| `admin-theme-layout` | `master` 主题/十色、Alpine、浏览器兼容 |
| `admin-new-module` | 新增完整后台模块的步骤清单 |
| `front-my-and-comments` | 前台 `my`、评论、`front.active` |
| `e2e-playwright` | Playwright 配置、`npm run test:e2e`、环境变量 |

Skills 由代理根据 `description` 中的关键词在相关任务时读取。


