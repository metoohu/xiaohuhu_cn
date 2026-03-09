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
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 text-slate-800" x-data="{ sidebarOpen: true }">
    <div class="flex">
        {{-- 侧边栏 --}}
        <aside class="w-64 bg-slate-800 text-slate-200 min-h-screen fixed lg:static transition-transform duration-200"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="p-4 border-b border-slate-700">
                <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold">{{ \App\Models\Setting::adminName() }}</a>
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
            <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex-1"></div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('front.home') }}" target="_blank" class="text-sm hover:text-blue-600">首页</a>
                    <a href="{{ route('admin.profile.edit') }}" class="text-sm hover:text-blue-600">{{ auth()->guard('admin')->user()->name }}</a>
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-700">退出</button>
                    </form>
                </div>
            </header>

            {{-- 内容区 --}}
            <main class="flex-1 p-4 lg:p-6">
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
    @stack('scripts')
</body>
</html>
