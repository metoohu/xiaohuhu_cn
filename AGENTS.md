# AI 代理协作指南（本仓库）

## Superpowers 工作流（默认）

新功能与多步改动须按顺序执行：

1. **Brainstorm** → `docs/superpowers/brainstorms/`（可选纪要）+ `docs/superpowers/specs/<feature>.md`
2. **Plan** → `docs/superpowers/plans/YYYY-MM-DD-<feature>.md`
3. **Execute** → 按 plan 勾选任务改代码
4. **Verify** → 运行测试并粘贴真实终端输出

总编排：`.cursor/skills/superpowers/SKILL.md`  
强制规则：`.cursor/rules/superpowers.mdc`（`alwaysApply`）

## 项目约定

- **框架**：Laravel 11；路由 `routes/web.php`（`front.*`）、`routes/admin.php`（`admin.*`，前缀 `/admin`）
- **认证**：前台 `web` + `App\Models\User`；后台 `admin` + `App\Models\Admin\AdminUser`
- **语言**：与用户沟通用**简体中文**；新增代码附简体中文注释
- **配置**：`config/admin.php`、`config/front.php`；密钥仅 `.env`

## 领域技能（plan 执行阶段）

| 技能目录 | 用途 |
|----------|------|
| `.cursor/skills/superpowers/` | **总编排（优先）** |
| `.cursor/skills/admin-new-module/` | 新增后台 CRUD 模块 |
| `.cursor/skills/admin-menu-sidebar/` | 侧栏菜单数据驱动 |
| `.cursor/skills/admin-theme-layout/` | 后台 master 主题 |
| `.cursor/skills/front-my-and-comments/` | 前台 my、评论 |
| `.cursor/skills/auto-text-generate-publish/` | AI/定时自动生成发布 |
| `.cursor/skills/e2e-playwright/` | Playwright E2E |

## 设计文档

- 目录说明：`docs/superpowers/README.md`
- Spec 范例：`docs/superpowers/specs/user-community-articles-design.md`

## Cursor 规则

见 `.cursor/README.md`；`laravel-core.mdc` 与 `superpowers.mdc` 始终生效。

## 验证（完工前）

```bash
php artisan test
php artisan route:list --name=<相关前缀>
```

详见 `.cursor/skills/superpowers/verification-before-completion/SKILL.md`。
