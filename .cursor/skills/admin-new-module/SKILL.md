---
name: admin-new-module
description: 为本项目新增一个完整的后台功能模块（路由、控制器、请求、视图、权限与菜单）。在用户要求「加后台页面、加管理功能、加 CRUD」且路径在 /admin 下时使用。
---

# 新增后台功能模块（清单）

## 1. 路由 `routes/admin.php`

- 写在 `middleware(['admin.auth'])` 组内。
- `->name('admin.模块.动作')` 全名唯一。
- 若有 `resource` 与 `xxx/export`、`xxx/batch` 等，**自定义路由写在 resource 前**（避免被 `{id}` 吃掉）。
- 与 `members`、`categories` 等类似冲突时，**更具体的路径靠前**。

## 2. 控制器

- 类放在 `App\Http\Controllers\Admin`。
- 方法返回类型：`View`、`RedirectResponse`、`JsonResponse` 等显式声明。
- 列表分页：`config('admin.per_page', 10)` 或与同类页面一致。
- 需要时记录 `AdminOperationLog`（参照已有控制器）。

## 3. 验证

- 创建 `App\Http\Requests\Admin\XxxRequest`，在 `store`/`update` 中注入。

## 4. 视图

- 路径 `resources/views/admin/模块/`；`@extends('admin.layouts.master')`。
- 表单 POST + `@csrf`；PUT/PATCH 加 `@method`。
- 成功/失败消息与 master 中 `session` 展示方式一致。

## 5. 侧栏入口

- 在 **菜单管理** 增加一条（`route_name` 指向新 `admin.*`），或写迁移/Seeder 插入 `admin_menu_items`。

## 6. 验证命令

```bash
php artisan route:list --name=admin.你的前缀
php artisan migrate   # 若新增表
```

## 7. 易混点

- 前台会员是 `User` + `admin.members.*`；后台账号是 `AdminUser` + `admin.users.*`，勿混用模型与策略。
