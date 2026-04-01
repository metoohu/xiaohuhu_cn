---
name: admin-menu-sidebar
description: 维护后台左侧菜单的数据表驱动能力：admin_menu_items 表、AdminMenuItem 模型、菜单管理 CRUD、侧栏 Blade 与路由绑定。在用户要增删改菜单项、排序、子菜单、路由名、高亮规则或菜单管理页时使用。
---

# 后台侧栏菜单（数据驱动）

## 数据与模型

- 表：`admin_menu_items`（含 `parent_id`、`title`、`route_name`、`url`、`active_pattern`、`sort`、`is_active`、`is_divider` 等，以迁移为准）。
- 模型：`App\Models\Admin\AdminMenuItem`：树形、`sidebarTree()`（侧栏用）、`managementTree()`（管理页用）、`resolveHref()`、`isRouteActive()`。

## 代码位置

| 用途 | 路径 |
|------|------|
| 迁移 | `database/migrations/*admin_menu_items*` |
| 控制器 | `app/Http/Controllers/Admin/AdminMenuItemController.php` |
| 视图 | `resources/views/admin/menu-items/*.blade.php`、`admin/partials/sidebar-nav.blade.php` |
| 路由 | `routes/admin.php`：`move-up` / `move-down` + `resource`（`parameters: menu-items → admin_menu_item`） |
| 注入菜单 | `AppServiceProvider`：`View::composer('admin.layouts.master', …)` 设置 `adminSidebarMenu` |

## 操作规则

1. **链接**：优先填 `route_name`（`admin.*`）；无路由时可填 `url`。
2. **高亮**：可填 `active_pattern`（如 `admin.menu-items.*`）；否则由 `route_name` 推断。
3. **删除**：递归删除子节点；注意外键或 `nullOnDelete` 与迁移一致。
4. **排序**：同父级交换 `sort`（上移/下移）；勿破坏父子关系。
5. **parent_id**：更新时防止闭环。
6. **侧栏 HTML**：`nav` + `ul`/`li`；链接 `admin-sidebar-link`；当前页 `aria-current="page"`；分隔线 `is_divider`。

## 新后台页面上线检查

- [ ] `routes/admin.php` 已注册且 `name()` 为 `admin.xxx`。
- [ ] 在「菜单管理」增加对应项（或 seeder），否则侧栏无入口（除非仅用 fallback）。
- [ ] `php artisan route:list --name=admin.menu-items` 与页面 CRUD、移动按钮无 404/405。

## 故障排查

- 侧栏空白提示 migrate：表未建或 composer 异常；执行 `php artisan migrate` 并看日志。
- 绑定失败：URI 参数名与控制器 `AdminMenuItem $admin_menu_item`、路由 `parameters` 一致。
