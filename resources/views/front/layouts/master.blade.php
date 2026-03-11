<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] ?? \App\Models\Setting::get('site_title', \App\Models\Setting::adminName()) ?: '心灵归宿' }}</title>
    <meta name="keywords" content="{{ $seo['keywords'] ?? \App\Models\Setting::seoKeywords() }}">
    <meta name="description" content="{{ $seo['description'] ?? \App\Models\Setting::seoDescription() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Noto Sans SC', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                        footer: { 800: '#1e3a8a', 900: '#1e293b' },
                        dark: { 800: '#1e293b', 900: '#0f172a', 950: '#020617' }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lazysizes@5.3.2/lazysizes.min.js" async></script>
    <style>
        [x-cloak] { display: none !important; }
        .prose img { border-radius: 0.5rem; }
    </style>
    @stack('styles')
</head>
<body class="bg-white text-slate-800 min-h-screen flex flex-col font-sans antialiased">
    {{-- 顶部导航（浅色） --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50" x-data="{ mobileMenuOpen: false }" @click.away="mobileMenuOpen = false">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14 md:h-16">
                <a href="{{ route('front.home') }}" class="text-xl font-bold text-slate-800 hover:text-primary-600 transition-colors">
                    {{ \App\Models\Setting::adminName() ?: '心灵归宿' }}
                </a>
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ route('front.home') }}" class="text-sm font-medium {{ request()->routeIs('front.home') ? 'text-primary-600' : 'text-slate-600 hover:text-primary-600' }} transition-colors">首页</a>
                    <a href="{{ route('front.categories.index') }}" class="text-sm font-medium {{ request()->routeIs('front.categories.*') ? 'text-primary-600' : 'text-slate-600 hover:text-primary-600' }} transition-colors">文章分类</a>
                    <a href="{{ route('front.news.index') }}" class="text-sm font-medium {{ request()->routeIs('front.news.*') ? 'text-primary-600' : 'text-slate-600 hover:text-primary-600' }} transition-colors">新闻资讯</a>
                    <a href="{{ route('front.about') }}" class="text-sm font-medium {{ request()->routeIs('front.about') ? 'text-primary-600' : 'text-slate-600 hover:text-primary-600' }} transition-colors">关于我</a>
                </nav>
                <div class="md:hidden">
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
        {{-- 移动端菜单 --}}
        <div class="md:hidden border-t border-slate-200" x-show="mobileMenuOpen" x-cloak x-transition>
            <div class="px-4 py-4 space-y-1 bg-slate-50">
                <a href="{{ route('front.home') }}" class="block py-2 text-sm text-slate-600 hover:text-primary-600">首页</a>
                <a href="{{ route('front.categories.index') }}" class="block py-2 text-sm text-slate-600 hover:text-primary-600">文章分类</a>
                <a href="{{ route('front.news.index') }}" class="block py-2 text-sm text-slate-600 hover:text-primary-600">新闻资讯</a>
                <a href="{{ route('front.about') }}" class="block py-2 text-sm text-slate-600 hover:text-primary-600">关于我</a>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- 页脚（深蓝） --}}
    <footer class="bg-footer-900 text-white mt-auto">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
            <h3 class="text-xl font-bold mb-3">{{ \App\Models\Setting::adminName() ?: '心灵归宿' }}</h3>
            <p class="text-slate-300 text-sm max-w-xl mx-auto leading-relaxed">记录学习历程，分享技术心得。与你一起在编程的世界里不断成长。</p>
            @if($icp = \App\Models\Setting::get('site_icp'))
            <p class="text-slate-500 text-xs mt-4">备案号：{{ $icp }}</p>
            @endif
            <p class="text-slate-500 text-xs mt-2">© {{ date('Y') }} {{ \App\Models\Setting::adminName() ?: '心灵归宿' }}</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
