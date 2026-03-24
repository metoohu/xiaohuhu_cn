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
        /* 主题变量 */
        [data-admin-theme="default"] { --admin-bg: #f1f5f9; --admin-bg-card: #ffffff; }
        [data-admin-theme="mint"] { --admin-bg: #e8f5f0; --admin-bg-card: #f0fdf7; }
        [data-admin-theme="lavender"] { --admin-bg: #f3e8f6; --admin-bg-card: #faf5fc; }
        [data-admin-theme="peach"] { --admin-bg: #fef3e8; --admin-bg-card: #fff9f5; }
        [data-admin-theme="sage"] { --admin-bg: #e8f0eb; --admin-bg-card: #f4f9f6; }

        body.admin-body { background-color: var(--admin-bg) !important; transition: background-color 0.4s ease; }
        .admin-main { transition: background-color 0.4s ease; }
        .admin-card { background-color: var(--admin-bg-card); transition: background-color 0.4s ease; }

        /* 表单输入框 - 边框与交互效果 */
        .admin-main input[type="text"],
        .admin-main input[type="email"],
        .admin-main input[type="password"],
        .admin-main input[type="number"],
        .admin-main input[type="file"],
        .admin-main select,
        .admin-main textarea {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 0.5rem;
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

        /* 侧边栏菜单 3D 效果 */
        .admin-sidebar nav a {
            position: relative;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 0.5rem;
        }
        .admin-sidebar nav a:hover {
            background: linear-gradient(145deg, #475569 0%, #334155 100%) !important;
            transform: translateX(4px) translateZ(0);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 2px 8px rgba(0,0,0,0.2);
        }
        .admin-sidebar nav a.bg-slate-700,
        .admin-sidebar nav a[class*="bg-slate-700"] {
            background: linear-gradient(145deg, #334155 0%, #1e293b 100%) !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.3), 0 1px 0 rgba(255,255,255,0.05);
            transform: translateX(2px);
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
<body class="min-h-screen text-slate-800 admin-body" style="background-color: var(--admin-bg);" data-admin-theme="default" x-data="{ sidebarOpen: true }">
    <div class="flex">
        {{-- 侧边栏 --}}
        <aside class="admin-sidebar w-64 bg-slate-800 text-slate-200 min-h-screen fixed lg:static transition-transform duration-200 shadow-[4px_0_24px_rgba(0,0,0,0.15)]"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="p-4 border-b border-slate-700 shadow-[inset_0_-1px_0_rgba(0,0,0,0.2)]">
                <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold block transition opacity-90 hover:opacity-100">{{ \App\Models\Setting::adminName() }}</a>
            </div>
            <nav class="p-2 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('admin.dashboard') ? 'bg-slate-700' : '' }}">仪表盘</a>
                <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('admin.users.*') ? 'bg-slate-700' : '' }}">用户管理</a>
                <a href="{{ route('admin.roles.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('admin.roles.*') ? 'bg-slate-700' : '' }}">角色管理</a>
                <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('admin.categories.*') ? 'bg-slate-700' : '' }}">分类管理</a>
                <a href="{{ route('admin.articles.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('admin.articles.*') ? 'bg-slate-700' : '' }}">文章管理</a>
                <a href="{{ route('admin.comments.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('admin.comments.*') ? 'bg-slate-700' : '' }}">评论管理</a>
                <div class="border-t border-slate-700 my-2"></div>
                <a href="{{ route('admin.settings.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('admin.settings.*') ? 'bg-slate-700' : '' }}">系统设置</a>
                <a href="{{ route('admin.logs.operation') }}" class="block px-3 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('admin.logs.*') ? 'bg-slate-700' : '' }}">操作日志</a>
                <a href="{{ route('admin.backups.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('admin.backups.*') ? 'bg-slate-700' : '' }}">备份管理</a>
            </nav>
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
                             class="absolute right-0 mt-1 w-40 py-1 bg-white rounded-xl shadow-xl border border-slate-200 z-50">
                            <button type="button" @click="$store.adminTheme.set('default'); themeOpen = false" class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 active:bg-slate-100 transition-colors flex items-center gap-2 rounded-lg mx-1">
                                <span class="w-3 h-3 rounded-full bg-slate-200"></span> 默认灰
                            </button>
                            <button type="button" @click="$store.adminTheme.set('mint'); themeOpen = false" class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 active:bg-slate-100 transition-colors flex items-center gap-2 rounded-lg mx-1">
                                <span class="w-3 h-3 rounded-full bg-emerald-200"></span> 薄荷绿
                            </button>
                            <button type="button" @click="$store.adminTheme.set('lavender'); themeOpen = false" class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 active:bg-slate-100 transition-colors flex items-center gap-2 rounded-lg mx-1">
                                <span class="w-3 h-3 rounded-full bg-purple-200"></span> 薰衣草
                            </button>
                            <button type="button" @click="$store.adminTheme.set('peach'); themeOpen = false" class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 active:bg-slate-100 transition-colors flex items-center gap-2 rounded-lg mx-1">
                                <span class="w-3 h-3 rounded-full bg-orange-200"></span> 蜜桃
                            </button>
                            <button type="button" @click="$store.adminTheme.set('sage'); themeOpen = false" class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 active:bg-slate-100 transition-colors flex items-center gap-2 rounded-lg mx-1">
                                <span class="w-3 h-3 rounded-full bg-teal-200"></span> 鼠尾草
                            </button>
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
            const saved = localStorage.getItem('admin-theme') || 'default';
            Alpine.store('adminTheme', {
                current: saved,
                set(theme) {
                    this.current = theme;
                    document.body.setAttribute('data-admin-theme', theme);
                    localStorage.setItem('admin-theme', theme);
                }
            });
            document.body.setAttribute('data-admin-theme', saved);
        });
    </script>
    @stack('scripts')
</body>
</html>
