<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] ?? \App\Models\Setting::get('site_title', \App\Models\Setting::adminName()) ?: '小糊涂人生馆' }}</title>
    <meta name="keywords" content="{{ $seo['keywords'] ?? \App\Models\Setting::seoKeywords() }}">
    <meta name="description" content="{{ $seo['description'] ?? \App\Models\Setting::seoDescription() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@400;500;600;700&family=Noto+Sans+SC:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Noto Sans SC', 'system-ui', 'sans-serif'],
                        serif: ['Noto Serif SC', 'Georgia', 'serif']
                    },
                    colors: {
                        primary: { 50: '#f0f7fa', 100: '#e4f0f5', 200: '#cce4ed', 300: '#b0d4e3', 400: '#8fc4d8', 500: '#6eb3cc', 600: '#5a9bb5', 700: '#4a7f96', 800: '#3d6478', 900: '#2d4a5c' },
                        haze: { 50: '#f5f9fb', 100: '#e8f2f7', 200: '#d4e8f0', 300: '#b8d4e3', 400: '#9bc0d6', 500: '#7ea9c4' },
                        footer: { 800: '#3d6478', 900: '#2d4a5c' },
                        dark: { 800: '#4a5f6d', 900: '#3d5260', 950: '#2d3d4a' }
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
        .prose { font-family: 'Noto Serif SC', Georgia, serif; }
        .prose p { line-height: 1.9; }
    </style>
    @stack('styles')
</head>
<body class="bg-haze-50 text-dark-900 min-h-screen flex flex-col font-sans antialiased">
    {{-- 顶部导航（雾霾蓝治愈系） --}}
    <header class="bg-white/80 backdrop-blur-sm border-b border-haze-200 sticky top-0 z-50 shadow-sm shadow-haze-100/50" x-data="{ mobileMenuOpen: false }" @click.away="mobileMenuOpen = false">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14 md:h-16">
                <a href="{{ route('front.home') }}" class="text-xl font-serif font-semibold text-primary-700 hover:text-primary-600 transition-colors">
                    {{ \App\Models\Setting::adminName() ?: '小糊涂人生馆' }}
                </a>
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ route('front.home') }}" class="text-sm font-medium {{ request()->routeIs('front.home') ? 'text-primary-600' : 'text-dark-800/70 hover:text-primary-600' }} transition-colors">首页</a>
                    <a href="{{ route('front.categories.index') }}" class="text-sm font-medium {{ request()->routeIs('front.categories.*') ? 'text-primary-600' : 'text-dark-800/70 hover:text-primary-600' }} transition-colors">文章分类</a>
                    <a href="{{ route('front.news.index') }}" class="text-sm font-medium {{ request()->routeIs('front.news.*') ? 'text-primary-600' : 'text-dark-800/70 hover:text-primary-600' }} transition-colors">新闻资讯</a>
                    <a href="{{ route('front.about') }}" class="text-sm font-medium {{ request()->routeIs('front.about') ? 'text-primary-600' : 'text-dark-800/70 hover:text-primary-600' }} transition-colors">关于我</a>
                </nav>
                <div class="md:hidden">
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-primary-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
        {{-- 移动端菜单 --}}
        <div class="md:hidden border-t border-haze-200" x-show="mobileMenuOpen" x-cloak x-transition>
            <div class="px-4 py-4 space-y-1 bg-haze-50">
                <a href="{{ route('front.home') }}" class="block py-2 text-sm text-dark-800/70 hover:text-primary-600">首页</a>
                <a href="{{ route('front.categories.index') }}" class="block py-2 text-sm text-dark-800/70 hover:text-primary-600">文章分类</a>
                <a href="{{ route('front.news.index') }}" class="block py-2 text-sm text-dark-800/70 hover:text-primary-600">新闻资讯</a>
                <a href="{{ route('front.about') }}" class="block py-2 text-sm text-dark-800/70 hover:text-primary-600">关于我</a>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- 页脚（雾霾蓝治愈系） --}}
    <footer class="bg-primary-800 text-white mt-auto">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
            <h3 class="text-xl font-serif font-semibold mb-3 text-haze-100">{{ \App\Models\Setting::adminName() ?: '小糊涂人生馆' }}</h3>
            <p class="text-haze-200 text-sm max-w-xl mx-auto leading-relaxed">人间清醒，治愈文字。在喧嚣中寻一方宁静，用文字温暖你我。</p>
            @if($icp = \App\Models\Setting::get('site_icp'))
            <p class="text-haze-300/80 text-xs mt-4">备案号：{{ $icp }}</p>
            @endif
            <p class="text-haze-300/70 text-xs mt-2">© {{ date('Y') }} {{ \App\Models\Setting::adminName() ?: '小糊涂人生馆' }}</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
