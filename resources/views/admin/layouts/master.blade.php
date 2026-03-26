<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Models\Setting::adminName())</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* 后台色板（与设计稿一致） */
        :root {
            --admin-c-space: #1E293B;
            --admin-c-neutral: #334155;
            --admin-c-carbon: #121212;
            --admin-c-mist-blue: #475569;
            --admin-c-pale: #F1F5F9;
            --admin-c-pale-border: #E2E8F0;
            --admin-c-serene: #0F172A;
            --admin-c-graphite: #27272A;
            --admin-c-haze: #384B62;
            --admin-c-ivory: #F8FAFC;
        }
        /* 主内容区背景：与工作区 10 色一致（卡片保持浅色以便阅读） */
        [data-admin-theme="t-f8fc"],
        [data-admin-theme="f8fc"],
        [data-admin-theme="peach"] { --admin-bg: #F8FAFC; --admin-bg-card: #ffffff; }
        [data-admin-theme="t-f1f5"],
        [data-admin-theme="f1f5"],
        [data-admin-theme="default"],
        [data-admin-theme="mint"],
        [data-admin-theme="sage"] { --admin-bg: #F1F5F9; --admin-bg-card: #ffffff; }
        [data-admin-theme="t-e2e8"],
        [data-admin-theme="e2e8"],
        [data-admin-theme="lavender"] { --admin-bg: #E2E8F0; --admin-bg-card: #ffffff; }
        [data-admin-theme="t-475569"] { --admin-bg: #475569; --admin-bg-card: #ffffff; }
        [data-admin-theme="t-1e293b"] { --admin-bg: #1E293B; --admin-bg-card: #ffffff; }
        [data-admin-theme="t-334155"] { --admin-bg: #334155; --admin-bg-card: #ffffff; }
        [data-admin-theme="t-384b62"] { --admin-bg: #384B62; --admin-bg-card: #ffffff; }
        [data-admin-theme="t-27272a"] { --admin-bg: #27272A; --admin-bg-card: #ffffff; }
        [data-admin-theme="t-0f172a"] { --admin-bg: #0F172A; --admin-bg-card: #ffffff; }
        [data-admin-theme="t-121212"] { --admin-bg: #121212; --admin-bg-card: #ffffff; }

        body.admin-body { background-color: var(--admin-bg) !important; transition: background-color 0.4s ease; }
        .admin-main { transition: background-color 0.4s ease; }
        .admin-card { background-color: var(--admin-bg-card); transition: background-color 0.4s ease; }

        /* 表单输入框 - 高度 45px、边框与交互效果 */
        .admin-main input[type="text"],
        .admin-main input[type="email"],
        .admin-main input[type="password"],
        .admin-main input[type="number"],
        .admin-main select {
            height: 45px !important;
            min-height: 45px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 0.5rem;
            box-sizing: border-box;
            padding: 0 0.75rem;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
        }
        .admin-main input[type="file"] {
            min-height: 45px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
        }
        .admin-main textarea {
            min-height: 90px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 0.5rem;
            padding: 0.75rem;
            box-sizing: border-box;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
        }
        .admin-main input[type="text"]:hover,
        .admin-main input[type="email"]:hover,
        .admin-main input[type="password"]:hover,
        .admin-main input[type="number"]:hover,
        .admin-main input[type="file"]:hover,
        .admin-main select:hover,
        .admin-main textarea:hover {
            border-color: #94a3b8 !important;
            background-color: #fcfdfe;
        }
        .admin-main input[type="text"]:focus,
        .admin-main input[type="email"]:focus,
        .admin-main input[type="password"]:focus,
        .admin-main input[type="number"]:focus,
        .admin-main input[type="file"]:focus,
        .admin-main select:focus,
        .admin-main textarea:focus {
            border-color: #475569 !important;
            box-shadow: 0 0 0 3px rgba(71, 85, 105, 0.12);
            outline: none;
        }
        /* 卡片随主题变色 + 3D 效果 */
        .admin-main .bg-white.rounded-lg {
            background-color: var(--admin-bg-card) !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -2px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.04);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .admin-main .bg-white.rounded-lg:hover {
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.08), 0 0 0 1px rgba(0,0,0,0.05);
            transform: translateY(-2px);
        }

        /* 侧栏：简约层级 + HTML5 焦点环（:focus-visible） */
        .admin-sidebar-link {
            position: relative;
            color: var(--admin-c-neutral);
            -webkit-tap-highlight-color: transparent;
        }
        .admin-sidebar-link:hover {
            background-color: rgba(30, 41, 59, 0.06);
            color: var(--admin-c-space);
        }
        .admin-sidebar-link.nav-active {
            background-color: rgba(30, 41, 59, 0.09);
            color: var(--admin-c-serene);
            font-weight: 600;
            box-shadow: inset 3px 0 0 0 var(--admin-c-mist-blue);
        }
        .admin-sidebar-group {
            color: var(--admin-c-mist-blue);
        }
        .admin-sidebar-nested {
            border-left: 1px solid rgba(51, 65, 85, 0.18);
        }
        .admin-nav-hr {
            border-top-width: 1px;
            border-top-color: rgba(148, 163, 184, 0.4);
        }
        .admin-sidebar-link:focus-visible {
            outline: 2px solid var(--admin-c-mist-blue);
            outline-offset: 2px;
        }
        .admin-sidebar-link:focus:not(:focus-visible) {
            outline: none;
        }

        /* 侧栏 10 色（与色板一一对应，键名即 hex 便于维护） */
        [data-admin-sidebar="sb-f8fc"] { --admin-sb-bg: #F8FAFC; --admin-sb-scheme: light; }
        [data-admin-sidebar="sb-f1f5"] { --admin-sb-bg: #F1F5F9; --admin-sb-scheme: light; }
        [data-admin-sidebar="sb-e2e8"] { --admin-sb-bg: #E2E8F0; --admin-sb-scheme: light; }
        [data-admin-sidebar="sb-475569"] { --admin-sb-bg: #475569; --admin-sb-scheme: dark; }
        [data-admin-sidebar="sb-1e293b"] { --admin-sb-bg: #1E293B; --admin-sb-scheme: dark; }
        [data-admin-sidebar="sb-334155"] { --admin-sb-bg: #334155; --admin-sb-scheme: dark; }
        [data-admin-sidebar="sb-384b62"] { --admin-sb-bg: #384B62; --admin-sb-scheme: dark; }
        [data-admin-sidebar="sb-27272a"] { --admin-sb-bg: #27272A; --admin-sb-scheme: dark; }
        [data-admin-sidebar="sb-0f172a"] { --admin-sb-bg: #0F172A; --admin-sb-scheme: dark; }
        [data-admin-sidebar="sb-121212"] { --admin-sb-bg: #121212; --admin-sb-scheme: dark; }

        .admin-sidebar {
            background-color: var(--admin-sb-bg, var(--admin-c-ivory));
            border-right: 1px solid var(--admin-sb-border, var(--admin-c-pale-border));
        }
        [data-admin-sidebar="sb-f8fc"] .admin-sidebar,
        [data-admin-sidebar="sb-f1f5"] .admin-sidebar,
        [data-admin-sidebar="sb-e2e8"] .admin-sidebar {
            color: var(--admin-c-neutral);
            --admin-sb-border: #E2E8F0;
        }
        [data-admin-sidebar="sb-475569"] .admin-sidebar,
        [data-admin-sidebar="sb-1e293b"] .admin-sidebar,
        [data-admin-sidebar="sb-334155"] .admin-sidebar,
        [data-admin-sidebar="sb-384b62"] .admin-sidebar,
        [data-admin-sidebar="sb-27272a"] .admin-sidebar,
        [data-admin-sidebar="sb-0f172a"] .admin-sidebar,
        [data-admin-sidebar="sb-121212"] .admin-sidebar {
            color: var(--admin-c-ivory);
            --admin-sb-border: rgba(0, 0, 0, 0.22);
        }
        [data-admin-sidebar="sb-121212"] .admin-sidebar {
            --admin-sb-border: rgba(255, 255, 255, 0.06);
        }

        [data-admin-sidebar="sb-f8fc"] .admin-sidebar-link,
        [data-admin-sidebar="sb-f1f5"] .admin-sidebar-link,
        [data-admin-sidebar="sb-e2e8"] .admin-sidebar-link {
            color: var(--admin-c-neutral);
        }
        [data-admin-sidebar="sb-f8fc"] .admin-sidebar-link:hover,
        [data-admin-sidebar="sb-f1f5"] .admin-sidebar-link:hover,
        [data-admin-sidebar="sb-e2e8"] .admin-sidebar-link:hover {
            background-color: rgba(30, 41, 59, 0.06);
            color: var(--admin-c-space);
        }
        [data-admin-sidebar="sb-f8fc"] .admin-sidebar-link.nav-active,
        [data-admin-sidebar="sb-f1f5"] .admin-sidebar-link.nav-active,
        [data-admin-sidebar="sb-e2e8"] .admin-sidebar-link.nav-active {
            background-color: rgba(30, 41, 59, 0.09);
            color: var(--admin-c-serene);
            box-shadow: inset 3px 0 0 0 var(--admin-c-mist-blue);
        }
        [data-admin-sidebar="sb-f8fc"] .admin-sidebar-group,
        [data-admin-sidebar="sb-f1f5"] .admin-sidebar-group,
        [data-admin-sidebar="sb-e2e8"] .admin-sidebar-group {
            color: var(--admin-c-mist-blue);
        }
        [data-admin-sidebar="sb-f8fc"] .admin-sidebar-nested,
        [data-admin-sidebar="sb-f1f5"] .admin-sidebar-nested,
        [data-admin-sidebar="sb-e2e8"] .admin-sidebar-nested {
            border-left-color: rgba(51, 65, 85, 0.18);
        }
        [data-admin-sidebar="sb-f8fc"] .admin-nav-hr,
        [data-admin-sidebar="sb-f1f5"] .admin-nav-hr,
        [data-admin-sidebar="sb-e2e8"] .admin-nav-hr {
            border-top-color: rgba(148, 163, 184, 0.4);
        }
        [data-admin-sidebar="sb-f8fc"] .admin-sidebar-link:focus-visible,
        [data-admin-sidebar="sb-f1f5"] .admin-sidebar-link:focus-visible,
        [data-admin-sidebar="sb-e2e8"] .admin-sidebar-link:focus-visible {
            outline-color: var(--admin-c-mist-blue);
        }

        [data-admin-sidebar="sb-475569"] .admin-sidebar-link,
        [data-admin-sidebar="sb-1e293b"] .admin-sidebar-link,
        [data-admin-sidebar="sb-334155"] .admin-sidebar-link,
        [data-admin-sidebar="sb-384b62"] .admin-sidebar-link,
        [data-admin-sidebar="sb-27272a"] .admin-sidebar-link,
        [data-admin-sidebar="sb-0f172a"] .admin-sidebar-link,
        [data-admin-sidebar="sb-121212"] .admin-sidebar-link {
            color: rgba(248, 250, 252, 0.9);
        }
        [data-admin-sidebar="sb-475569"] .admin-sidebar-link:hover,
        [data-admin-sidebar="sb-1e293b"] .admin-sidebar-link:hover,
        [data-admin-sidebar="sb-334155"] .admin-sidebar-link:hover,
        [data-admin-sidebar="sb-384b62"] .admin-sidebar-link:hover,
        [data-admin-sidebar="sb-27272a"] .admin-sidebar-link:hover,
        [data-admin-sidebar="sb-0f172a"] .admin-sidebar-link:hover,
        [data-admin-sidebar="sb-121212"] .admin-sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        [data-admin-sidebar="sb-475569"] .admin-sidebar-link.nav-active,
        [data-admin-sidebar="sb-1e293b"] .admin-sidebar-link.nav-active,
        [data-admin-sidebar="sb-334155"] .admin-sidebar-link.nav-active,
        [data-admin-sidebar="sb-384b62"] .admin-sidebar-link.nav-active,
        [data-admin-sidebar="sb-27272a"] .admin-sidebar-link.nav-active,
        [data-admin-sidebar="sb-0f172a"] .admin-sidebar-link.nav-active,
        [data-admin-sidebar="sb-121212"] .admin-sidebar-link.nav-active {
            background-color: rgba(0, 0, 0, 0.22);
            color: #fff;
            box-shadow: inset 3px 0 0 0 var(--admin-c-pale-border);
        }
        [data-admin-sidebar="sb-475569"] .admin-sidebar-group,
        [data-admin-sidebar="sb-1e293b"] .admin-sidebar-group,
        [data-admin-sidebar="sb-334155"] .admin-sidebar-group,
        [data-admin-sidebar="sb-384b62"] .admin-sidebar-group,
        [data-admin-sidebar="sb-27272a"] .admin-sidebar-group,
        [data-admin-sidebar="sb-0f172a"] .admin-sidebar-group,
        [data-admin-sidebar="sb-121212"] .admin-sidebar-group {
            color: rgba(248, 250, 252, 0.45);
        }
        [data-admin-sidebar="sb-475569"] .admin-sidebar-nested,
        [data-admin-sidebar="sb-1e293b"] .admin-sidebar-nested,
        [data-admin-sidebar="sb-334155"] .admin-sidebar-nested,
        [data-admin-sidebar="sb-384b62"] .admin-sidebar-nested,
        [data-admin-sidebar="sb-27272a"] .admin-sidebar-nested,
        [data-admin-sidebar="sb-0f172a"] .admin-sidebar-nested,
        [data-admin-sidebar="sb-121212"] .admin-sidebar-nested {
            border-left-color: rgba(248, 250, 252, 0.12);
        }
        [data-admin-sidebar="sb-475569"] .admin-nav-hr,
        [data-admin-sidebar="sb-1e293b"] .admin-nav-hr,
        [data-admin-sidebar="sb-334155"] .admin-nav-hr,
        [data-admin-sidebar="sb-384b62"] .admin-nav-hr,
        [data-admin-sidebar="sb-27272a"] .admin-nav-hr,
        [data-admin-sidebar="sb-0f172a"] .admin-nav-hr,
        [data-admin-sidebar="sb-121212"] .admin-nav-hr {
            border-top-color: rgba(248, 250, 252, 0.12);
        }
        [data-admin-sidebar="sb-475569"] .admin-sidebar-link:focus-visible,
        [data-admin-sidebar="sb-1e293b"] .admin-sidebar-link:focus-visible,
        [data-admin-sidebar="sb-334155"] .admin-sidebar-link:focus-visible,
        [data-admin-sidebar="sb-384b62"] .admin-sidebar-link:focus-visible,
        [data-admin-sidebar="sb-27272a"] .admin-sidebar-link:focus-visible,
        [data-admin-sidebar="sb-0f172a"] .admin-sidebar-link:focus-visible,
        [data-admin-sidebar="sb-121212"] .admin-sidebar-link:focus-visible {
            outline-color: rgba(248, 250, 252, 0.85);
        }

        .admin-sidebar-header {
            border-bottom: 1px solid var(--admin-c-pale-border);
            background: rgba(255, 255, 255, 0.45);
        }
        [data-admin-sidebar="sb-f1f5"] .admin-sidebar-header,
        [data-admin-sidebar="sb-e2e8"] .admin-sidebar-header {
            background: rgba(255, 255, 255, 0.55);
        }
        [data-admin-sidebar="sb-475569"] .admin-sidebar-header,
        [data-admin-sidebar="sb-1e293b"] .admin-sidebar-header,
        [data-admin-sidebar="sb-334155"] .admin-sidebar-header,
        [data-admin-sidebar="sb-384b62"] .admin-sidebar-header,
        [data-admin-sidebar="sb-27272a"] .admin-sidebar-header,
        [data-admin-sidebar="sb-0f172a"] .admin-sidebar-header,
        [data-admin-sidebar="sb-121212"] .admin-sidebar-header {
            border-bottom-color: rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.18);
        }
        .admin-sidebar-brand {
            color: var(--admin-c-space);
        }
        [data-admin-sidebar="sb-475569"] .admin-sidebar-brand,
        [data-admin-sidebar="sb-1e293b"] .admin-sidebar-brand,
        [data-admin-sidebar="sb-334155"] .admin-sidebar-brand,
        [data-admin-sidebar="sb-384b62"] .admin-sidebar-brand,
        [data-admin-sidebar="sb-27272a"] .admin-sidebar-brand,
        [data-admin-sidebar="sb-0f172a"] .admin-sidebar-brand,
        [data-admin-sidebar="sb-121212"] .admin-sidebar-brand {
            color: var(--admin-c-ivory);
        }

        .admin-sidebar-footer {
            border-top: 1px solid var(--admin-c-pale-border);
            background: rgba(248, 250, 252, 0.75);
            backdrop-filter: blur(8px);
        }
        [data-admin-sidebar="sb-f1f5"] .admin-sidebar-footer {
            background: rgba(241, 245, 249, 0.88);
        }
        [data-admin-sidebar="sb-e2e8"] .admin-sidebar-footer {
            background: rgba(226, 232, 240, 0.92);
        }
        [data-admin-sidebar="sb-475569"] .admin-sidebar-footer,
        [data-admin-sidebar="sb-1e293b"] .admin-sidebar-footer,
        [data-admin-sidebar="sb-334155"] .admin-sidebar-footer,
        [data-admin-sidebar="sb-384b62"] .admin-sidebar-footer,
        [data-admin-sidebar="sb-27272a"] .admin-sidebar-footer,
        [data-admin-sidebar="sb-0f172a"] .admin-sidebar-footer,
        [data-admin-sidebar="sb-121212"] .admin-sidebar-footer {
            border-top-color: rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(10px);
        }
        .admin-sidebar-palette-label {
            color: #64748b;
        }
        [data-admin-sidebar="sb-475569"] .admin-sidebar-palette-label,
        [data-admin-sidebar="sb-1e293b"] .admin-sidebar-palette-label,
        [data-admin-sidebar="sb-334155"] .admin-sidebar-palette-label,
        [data-admin-sidebar="sb-384b62"] .admin-sidebar-palette-label,
        [data-admin-sidebar="sb-27272a"] .admin-sidebar-palette-label,
        [data-admin-sidebar="sb-0f172a"] .admin-sidebar-palette-label,
        [data-admin-sidebar="sb-121212"] .admin-sidebar-palette-label {
            color: rgba(248, 250, 252, 0.6) !important;
        }

        .admin-swatch-btn {
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }
        .admin-swatch-btn:focus-visible {
            outline: 2px solid var(--admin-c-mist-blue);
            outline-offset: 2px;
        }
        .admin-swatch-ring-light {
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #475569;
        }
        .admin-swatch-ring-dark {
            box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.5), 0 0 0 4px rgba(248, 250, 252, 0.9);
        }

        @media (prefers-reduced-motion: reduce) {
            .admin-sidebar, .admin-sidebar-link, .admin-sidebar * {
                transition-duration: 0.01ms !important;
            }
        }

        /* 菜单排序模式 */
        body.admin-nav-sort-mode .admin-nav-item {
            cursor: grab;
        }
        body.admin-nav-sort-mode .admin-nav-item:active {
            cursor: grabbing;
        }
        body.admin-nav-sort-mode .admin-nav-item.dragging {
            opacity: 0.65;
        }
        body.admin-nav-sort-mode .admin-sidebar .admin-sidebar-link {
            pointer-events: none;
            cursor: inherit;
        }
        body.admin-nav-sort-mode .admin-sidebar-nav-tools {
            outline: 2px dashed rgba(71, 85, 105, 0.45);
            outline-offset: 2px;
            border-radius: 0.5rem;
        }

        /* 主按钮 - 边框与 3D 效果 (深色) */
        .admin-main a.bg-slate-800,
        .admin-main a.bg-slate-600,
        .admin-main button.bg-slate-800,
        .admin-main button.bg-slate-600,
        .admin-main a.bg-slate-700 {
            border: 1.5px solid rgba(0,0,0,0.2) !important;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .admin-main a.bg-slate-800:hover,
        .admin-main a.bg-slate-600:hover,
        .admin-main button.bg-slate-800:hover,
        .admin-main button.bg-slate-600:hover,
        .admin-main a.bg-slate-700:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            border-color: rgba(0,0,0,0.25) !important;
        }
        .admin-main a.bg-slate-800:active,
        .admin-main a.bg-slate-600:active,
        .admin-main button.bg-slate-800:active,
        .admin-main button.bg-slate-600:active {
            transform: translateY(0);
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        /* 次要按钮 - 边框与 3D 效果 (灰色) */
        .admin-main a.bg-slate-200,
        .admin-main button.bg-slate-200,
        .admin-main a.bg-slate-300 {
            border: 1.5px solid #94a3b8 !important;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .admin-main a.bg-slate-200:hover,
        .admin-main button.bg-slate-200:hover,
        .admin-main a.bg-slate-300:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            border-color: #64748b !important;
        }
        .admin-main a.bg-slate-200:active,
        .admin-main button.bg-slate-200:active {
            transform: translateY(0);
        }

        /* 彩色按钮 - 边框与 3D (绿/琥珀/红) */
        .admin-main a.bg-green-600,
        .admin-main a.bg-amber-600,
        .admin-main button.bg-green-600,
        .admin-main button.bg-amber-600 {
            border: 1.5px solid rgba(0,0,0,0.15) !important;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .admin-main a.bg-green-100,
        .admin-main a.bg-amber-100,
        .admin-main a.bg-red-100,
        .admin-main a.bg-slate-100,
        .admin-main button.bg-green-100,
        .admin-main button.bg-amber-100,
        .admin-main button.bg-red-100,
        .admin-main button.bg-slate-100 {
            border: 1.5px solid rgba(148, 163, 184, 0.6) !important;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .admin-main a.bg-green-600:hover, .admin-main button.bg-green-600:hover,
        .admin-main a.bg-amber-600:hover, .admin-main button.bg-amber-600:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .admin-main a.bg-green-100:hover, .admin-main a.bg-amber-100:hover, .admin-main a.bg-red-100:hover, .admin-main a.bg-slate-100:hover,
        .admin-main button.bg-green-100:hover, .admin-main button.bg-amber-100:hover, .admin-main button.bg-red-100:hover, .admin-main button.bg-slate-100:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-color: #94a3b8 !important;
        }

        /* 表格行交互 */
        .admin-main table tbody tr {
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .admin-main table tbody tr:hover {
            background-color: rgba(148, 163, 184, 0.08);
        }
        .admin-main table tbody tr:hover td {
            position: relative;
        }

        /* 操作链接悬停 */
        .admin-main table a.text-blue-600,
        .admin-main table a.text-red-600,
        .admin-main table button.text-red-600 {
            transition: color 0.2s ease, opacity 0.2s ease;
        }
        .admin-main table a.text-blue-600:hover,
        .admin-main table a.text-red-600:hover,
        .admin-main table button.text-red-600:hover {
            opacity: 0.8;
        }

        /* 仪表盘统计卡片 3D */
        .admin-main .grid .bg-white.rounded-lg.shadow {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .admin-main .grid .bg-white.rounded-lg.shadow:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 12px 28px -8px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.04);
        }

        /* 顶部 header 链接/按钮 - 边框 */
        header button[type="button"],
        header button[type="submit"] {
            border: 1px solid transparent;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        header button[type="button"]:hover,
        header button[type="submit"]:hover {
            border-color: #e2e8f0;
        }
        header a.rounded {
            border: 1px solid transparent;
            transition: border-color 0.2s ease;
        }
        header a.rounded:hover {
            border-color: #e2e8f0;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen text-slate-800 admin-body" style="background-color: var(--admin-bg);" data-admin-theme="t-f1f5" data-admin-sidebar="sb-f8fc" x-data="{ sidebarOpen: true }">
    <div class="flex">
        {{-- 侧栏：语义化 nav + 列表；配色来自色板与 $store.adminSidebar --}}
        <aside class="admin-sidebar w-64 min-h-screen fixed lg:static flex flex-col z-40 transition-transform duration-200 ease-out shadow-[4px_0_32px_rgba(15,23,42,0.06)]"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <header class="admin-sidebar-header shrink-0 p-4">
                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand text-lg font-semibold tracking-tight transition-colors duration-200 hover:opacity-90">{{ \App\Models\Setting::adminName() }}</a>
            </header>
            <nav id="admin-sidebar-nav" class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden px-2 pb-2" style="scroll-behavior: smooth;" aria-label="后台主导航">
                @if(isset($adminSidebarMenu) && $adminSidebarMenu->isNotEmpty())
                    @include('admin.partials.sidebar-nav', ['items' => $adminSidebarMenu, 'depth' => 0])
                @else
                    <div class="mx-2 mb-3 rounded-lg border border-amber-200/80 bg-amber-50/90 px-3 py-2 text-xs text-amber-900">
                        侧栏菜单未加载。请执行 <code class="rounded bg-white px-1">php artisan migrate</code> 后刷新。
                    </div>
                    <ul class="admin-sidebar-root space-y-0.5" role="list">
                        <li class="list-none">
                            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-link block rounded-md px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'nav-active' : '' }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>仪表盘</a>
                        </li>
                    </ul>
                @endif
            </nav>
            <footer class="admin-sidebar-footer admin-sidebar-nav-tools shrink-0 space-y-2.5 p-3 text-[11px] leading-tight">
                <p class="admin-sidebar-palette-label mb-0 font-semibold tracking-wide">侧栏 · 10 色</p>
                <div class="grid grid-cols-5 gap-2" role="group" aria-label="侧栏底色十选一">
                    <button type="button" title="象牙白 #F8FAFC" @click="$store.adminSidebar.set('sb-f8fc')" :class="['admin-swatch-btn mx-auto h-8 w-8 shrink-0 rounded-md border border-slate-300/65 transition-transform hover:scale-105 active:scale-95', $store.adminSidebar.current === 'sb-f8fc' ? 'admin-swatch-ring-light' : '']" style="background:#F8FAFC"></button>
                    <button type="button" title="浅灰 #F1F5F9" @click="$store.adminSidebar.set('sb-f1f5')" :class="['admin-swatch-btn mx-auto h-8 w-8 shrink-0 rounded-md border border-slate-300/65 transition-transform hover:scale-105 active:scale-95', $store.adminSidebar.current === 'sb-f1f5' ? 'admin-swatch-ring-light' : '']" style="background:#F1F5F9"></button>
                    <button type="button" title="淡青灰 #E2E8F0" @click="$store.adminSidebar.set('sb-e2e8')" :class="['admin-swatch-btn mx-auto h-8 w-8 shrink-0 rounded-md border border-slate-400/50 transition-transform hover:scale-105 active:scale-95', $store.adminSidebar.current === 'sb-e2e8' ? 'admin-swatch-ring-light' : '']" style="background:#E2E8F0"></button>
                    <button type="button" title="雾感蓝灰 #475569" @click="$store.adminSidebar.set('sb-475569')" :class="['admin-swatch-btn mx-auto h-8 w-8 shrink-0 rounded-md border border-black/15 transition-transform hover:scale-105 active:scale-95', $store.adminSidebar.current === 'sb-475569' ? 'admin-swatch-ring-dark' : '']" style="background:#475569"></button>
                    <button type="button" title="深空灰 #1E293B" @click="$store.adminSidebar.set('sb-1e293b')" :class="['admin-swatch-btn mx-auto h-8 w-8 shrink-0 rounded-md border border-black/20 transition-transform hover:scale-105 active:scale-95', $store.adminSidebar.current === 'sb-1e293b' ? 'admin-swatch-ring-dark' : '']" style="background:#1E293B"></button>
                    <button type="button" title="中性深灰 #334155" @click="$store.adminSidebar.set('sb-334155')" :class="['admin-swatch-btn mx-auto h-8 w-8 shrink-0 rounded-md border border-black/20 transition-transform hover:scale-105 active:scale-95', $store.adminSidebar.current === 'sb-334155' ? 'admin-swatch-ring-dark' : '']" style="background:#334155"></button>
                    <button type="button" title="雾霾蓝 #384B62" @click="$store.adminSidebar.set('sb-384b62')" :class="['admin-swatch-btn mx-auto h-8 w-8 shrink-0 rounded-md border border-black/15 transition-transform hover:scale-105 active:scale-95', $store.adminSidebar.current === 'sb-384b62' ? 'admin-swatch-ring-dark' : '']" style="background:#384B62"></button>
                    <button type="button" title="石墨灰 #27272A" @click="$store.adminSidebar.set('sb-27272a')" :class="['admin-swatch-btn mx-auto h-8 w-8 shrink-0 rounded-md border border-white/12 transition-transform hover:scale-105 active:scale-95', $store.adminSidebar.current === 'sb-27272a' ? 'admin-swatch-ring-dark' : '']" style="background:#27272A"></button>
                    <button type="button" title="静谧蓝 #0F172A" @click="$store.adminSidebar.set('sb-0f172a')" :class="['admin-swatch-btn mx-auto h-8 w-8 shrink-0 rounded-md border border-white/10 transition-transform hover:scale-105 active:scale-95', $store.adminSidebar.current === 'sb-0f172a' ? 'admin-swatch-ring-dark' : '']" style="background:#0F172A"></button>
                    <button type="button" title="炭黑 #121212" @click="$store.adminSidebar.set('sb-121212')" :class="['admin-swatch-btn mx-auto h-8 w-8 shrink-0 rounded-md border border-white/10 transition-transform hover:scale-105 active:scale-95', $store.adminSidebar.current === 'sb-121212' ? 'admin-swatch-ring-dark' : '']" style="background:#121212"></button>
                </div>
            </footer>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            {{-- 顶部导航 --}}
            <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between shadow-sm">
                <button @click="sidebarOpen = !sidebarOpen" class="admin-header-btn lg:hidden p-2 rounded-lg transition-all duration-200 hover:bg-slate-100 hover:shadow-inner active:scale-95">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex-1"></div>
                <div class="flex items-center gap-3">
                    {{-- 主题切换 --}}
                    <div class="relative" x-data="{ themeOpen: false }" @click.outside="themeOpen = false">
                        <button @click="themeOpen = !themeOpen"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-slate-100 hover:shadow-inner active:scale-[0.98] transition-all duration-200 text-sm"
                                title="切换背景主题">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343L12.657 5.686a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            <span class="text-slate-600">主题</span>
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="themeOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="absolute right-0 z-50 mt-1 w-[min(100vw-1rem,17rem)] rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                            <p class="mb-1.5 px-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">工作区 10 色</p>
                            <div class="grid grid-cols-2 gap-1">
                                <button type="button" @click="$store.adminTheme.set('t-f8fc'); themeOpen = false" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs hover:bg-slate-50 active:bg-slate-100">
                                    <span class="h-4 w-4 shrink-0 rounded border border-slate-300/60" style="background:#F8FAFC"></span><span class="truncate">象牙白</span>
                                </button>
                                <button type="button" @click="$store.adminTheme.set('t-f1f5'); themeOpen = false" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs hover:bg-slate-50 active:bg-slate-100">
                                    <span class="h-4 w-4 shrink-0 rounded border border-slate-300/60" style="background:#F1F5F9"></span><span class="truncate">浅灰</span>
                                </button>
                                <button type="button" @click="$store.adminTheme.set('t-e2e8'); themeOpen = false" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs hover:bg-slate-50 active:bg-slate-100">
                                    <span class="h-4 w-4 shrink-0 rounded border border-slate-400/50" style="background:#E2E8F0"></span><span class="truncate">淡青灰</span>
                                </button>
                                <button type="button" @click="$store.adminTheme.set('t-475569'); themeOpen = false" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs hover:bg-slate-50 active:bg-slate-100">
                                    <span class="h-4 w-4 shrink-0 rounded border border-slate-600" style="background:#475569"></span><span class="truncate">雾感蓝灰</span>
                                </button>
                                <button type="button" @click="$store.adminTheme.set('t-1e293b'); themeOpen = false" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs hover:bg-slate-50 active:bg-slate-100">
                                    <span class="h-4 w-4 shrink-0 rounded border border-slate-700" style="background:#1E293B"></span><span class="truncate">深空灰</span>
                                </button>
                                <button type="button" @click="$store.adminTheme.set('t-334155'); themeOpen = false" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs hover:bg-slate-50 active:bg-slate-100">
                                    <span class="h-4 w-4 shrink-0 rounded border border-slate-600" style="background:#334155"></span><span class="truncate">中性深灰</span>
                                </button>
                                <button type="button" @click="$store.adminTheme.set('t-384b62'); themeOpen = false" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs hover:bg-slate-50 active:bg-slate-100">
                                    <span class="h-4 w-4 shrink-0 rounded border border-slate-600" style="background:#384B62"></span><span class="truncate">雾霾蓝</span>
                                </button>
                                <button type="button" @click="$store.adminTheme.set('t-27272a'); themeOpen = false" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs hover:bg-slate-50 active:bg-slate-100">
                                    <span class="h-4 w-4 shrink-0 rounded border border-zinc-600" style="background:#27272A"></span><span class="truncate">石墨灰</span>
                                </button>
                                <button type="button" @click="$store.adminTheme.set('t-0f172a'); themeOpen = false" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs hover:bg-slate-50 active:bg-slate-100">
                                    <span class="h-4 w-4 shrink-0 rounded border border-slate-800" style="background:#0F172A"></span><span class="truncate">静谧蓝</span>
                                </button>
                                <button type="button" @click="$store.adminTheme.set('t-121212'); themeOpen = false" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs hover:bg-slate-50 active:bg-slate-100">
                                    <span class="h-4 w-4 shrink-0 rounded border border-zinc-800" style="background:#121212"></span><span class="truncate">炭黑</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('front.home') }}" target="_blank" class="text-sm px-2 py-1 rounded hover:text-blue-600 hover:bg-blue-50 transition-colors">首页</a>
                    <a href="{{ route('admin.profile.edit') }}" class="text-sm px-2 py-1 rounded hover:text-blue-600 hover:bg-blue-50 transition-colors">{{ auth()->guard('admin')->user()->name }}</a>
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm px-2 py-1 rounded text-red-600 hover:text-red-700 hover:bg-red-50 transition-colors">退出</button>
                    </form>
                </div>
            </header>

            {{-- 内容区 --}}
            <main class="admin-main flex-1 p-4 lg:p-6">
                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
                @endif
                @if (session('status'))
                    <div class="mb-4 p-3 bg-blue-100 text-blue-800 rounded">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            var themeKeys = {
                't-f8fc': 1, 't-f1f5': 1, 't-e2e8': 1, 't-475569': 1, 't-1e293b': 1,
                't-334155': 1, 't-384b62': 1, 't-27272a': 1, 't-0f172a': 1, 't-121212': 1,
                f1f5: 1, e2e8: 1, f8fc: 1
            };
            var themeLegacy = {
                default: 't-f1f5', mint: 't-f1f5', sage: 't-f1f5',
                lavender: 't-e2e8', peach: 't-f8fc',
                f1f5: 't-f1f5', e2e8: 't-e2e8', f8fc: 't-f8fc'
            };
            var savedTheme = localStorage.getItem('admin-theme') || 't-f1f5';
            if (themeLegacy[savedTheme]) {
                savedTheme = themeLegacy[savedTheme];
                localStorage.setItem('admin-theme', savedTheme);
            }
            if (!themeKeys[savedTheme]) {
                savedTheme = 't-f1f5';
                localStorage.setItem('admin-theme', savedTheme);
            }
            Alpine.store('adminTheme', {
                current: savedTheme,
                set(theme) {
                    if (!themeKeys[theme]) theme = 't-f1f5';
                    this.current = theme;
                    document.body.setAttribute('data-admin-theme', theme);
                    localStorage.setItem('admin-theme', theme);
                }
            });
            document.body.setAttribute('data-admin-theme', savedTheme);

            var sidebarKeys = {
                'sb-f8fc': 1, 'sb-f1f5': 1, 'sb-e2e8': 1, 'sb-475569': 1, 'sb-1e293b': 1,
                'sb-334155': 1, 'sb-384b62': 1, 'sb-27272a': 1, 'sb-0f172a': 1, 'sb-121212': 1
            };
            var sidebarLegacy = {
                ivory: 'sb-f8fc', pale: 'sb-f1f5', 'haze-blue': 'sb-384b62', graphite: 'sb-27272a',
                midnight: 'sb-0f172a', carbon: 'sb-121212',
                mint: 'sb-f8fc', mist: 'sb-f8fc', sage: 'sb-f8fc', peach: 'sb-f8fc', lilac: 'sb-f8fc', cream: 'sb-f8fc',
                slate: 'sb-f1f5', indigo: 'sb-0f172a', emerald: 'sb-f8fc', rose: 'sb-f8fc', zinc: 'sb-f1f5', blue: 'sb-384b62'
            };
            var savedSidebar = localStorage.getItem('admin-sidebar') || 'sb-f8fc';
            if (sidebarLegacy[savedSidebar]) {
                savedSidebar = sidebarLegacy[savedSidebar];
                localStorage.setItem('admin-sidebar', savedSidebar);
            }
            if (!sidebarKeys[savedSidebar]) {
                savedSidebar = 'sb-f8fc';
                localStorage.setItem('admin-sidebar', savedSidebar);
            }
            Alpine.store('adminSidebar', {
                current: savedSidebar,
                set(key) {
                    if (!sidebarKeys[key]) key = 'sb-f8fc';
                    this.current = key;
                    document.body.setAttribute('data-admin-sidebar', key);
                    localStorage.setItem('admin-sidebar', key);
                },
            });
            document.body.setAttribute('data-admin-sidebar', savedSidebar);
        });
    </script>
    @stack('scripts')
</body>
</html>
