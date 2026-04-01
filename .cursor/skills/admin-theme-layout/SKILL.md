---
name: admin-theme-layout
description: 修改后台主布局 resources/views/admin/layouts/master.blade.php：工作区十色主题、侧栏十色、Alpine store、localStorage 键、Vite 与 CDN Tailwind 双路径。在用户要改后台配色、侧栏、主题下拉、色盘布局或修复 Chrome/Edge/Safari 显示差异时使用。
---

# 后台主版面与主题

## 文件

- 主模板：`resources/views/admin/layouts/master.blade.php`（内含大块 `<style>` 与 Alpine 初始化脚本）。

## 主题状态存放位置

- **`data-admin-theme`、`data-admin-sidebar` 写在 `<html>` 上**，不是 `body`。
- Alpine 与持久化：`document.documentElement.setAttribute(...)`；`localStorage` 键 `admin-theme`、`admin-sidebar`。
- 初始化时需把旧键映射到新键（见脚本内 `themeLegacy` / `sidebarLegacy`）。

## CSS 变量约定

- 工作区：`--admin-bg`、`--admin-bg-card`；主题键 `t-f8fc`、`t-f1f5`、`t-e2e8` 及深色 `t-475569` … `t-121212`（见 master 内选择器）。
- 侧栏：`--admin-sb-bg`；侧栏键 `sb-f8fc` … `sb-121212`。
- `.admin-sidebar` 使用 `background-color: var(--admin-sb-bg, #F8FAFC)` 等，保证有十六进制回退。

## 浏览器兼容要点

1. **色盘网格**：类名 `.admin-sidebar-swatch-grid`（`grid-template-columns: repeat(5, minmax(0,1fr))`），勿只靠 Tailwind `grid-cols-5`（未进 bundle 时会变成一列）。
2. **色块边框**：`.admin-swatch-b-light` 等纯 CSS，勿用 `border-slate-300/65` 等可能缺失的任意类。
3. **毛玻璃**：同时写 `-webkit-backdrop-filter` 与 `backdrop-filter`；`@supports not` 里给不透明底色。
4. **深色侧栏**：`html[data-admin-sidebar=...] .admin-sidebar { color-scheme: dark; }`（浅色对应 `light`）。

## 资源加载分支

- 若存在 `public/build/manifest.json` 或 `hot`：`@vite` 加载 `app.css` + `app.js`。
- 否则：CDN Tailwind + Alpine；此时 **不能假设** 所有 Tailwind 工具类存在。

## 修改后自检

- [ ] 切换十种侧栏色与十种工作区色无报错、无「闪一下 wrong color」。
- [ ] 清空 localStorage 后默认值与 `<html>` 初始属性一致。
- [ ] 侧栏底部色块 5×2 排列在窄宽下仍可用（可横向滚动由 master 内 aside 控制）。
