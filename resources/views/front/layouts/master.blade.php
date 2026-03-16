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
                        primary: { 50: '#f5f8f6', 100: '#e8f0ed', 200: '#d1e2db', 300: '#a8c9bc', 400: '#7aab97', 500: '#6b8e82', 600: '#5a7d72', 700: '#4a6d63', 800: '#3d5b52', 900: '#2d4a40' },
                        haze: { 50: '#f9f7f5', 100: '#f0eee9', 200: '#e5e2dc', 300: '#d4d0c8' },
                        morandi: { 50: '#f9f7f5', 100: '#f0eee9', 200: '#6b8e82', 300: '#5a7d72', 400: '#4a6d63' },
                        footer: { 800: '#4a6d63', 900: '#3d5b52' },
                        dark: { 800: '#4a5f6d', 900: '#333', 950: '#2d3d4a' }
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
<body class="bg-[#f9f7f5] text-[#333] min-h-screen flex flex-col font-sans antialiased">
    {{-- 头部导航：Logo 左上角，导航居中 --}}
    @php
        $siteLogo = \App\Models\Setting::get('site_logo');
        $siteName = \App\Models\Setting::adminName() ?: '小糊涂人生馆';
    @endphp
    <header class="relative border-b border-[#eee] bg-white/95 sticky top-0 z-50" x-data="{ mobileMenuOpen: false }" @click.away="mobileMenuOpen = false">
        {{-- 登录/注册 固定右上角，不影响主菜单 --}}
        <div class="hidden md:flex absolute right-4 top-1/2 -translate-y-1/2 items-center gap-3">
            @auth
            <span class="text-[14px] text-[#777]">{{ Auth::user()->name }}</span>
            <form action="{{ route('front.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-[14px] text-[#6b8e82] hover:text-[#4a6d63] transition-colors px-2 py-1 rounded hover:bg-[#f5f8f6]">退出</button>
            </form>
            @else
            <a href="{{ route('front.login') }}" class="text-[14px] font-medium text-[#6b8e82] hover:text-[#4a6d63] transition-colors px-3 py-1.5 rounded hover:bg-[#f5f8f6]">登录</a>
            @if(\App\Models\Setting::get('register_enabled', '1') == '1')
            <a href="{{ route('front.register') }}" class="text-[14px] font-medium text-[#4a6d63] bg-[#e8f0ed] hover:bg-[#d1e2db] text-[#4a6d63] transition-colors px-3 py-1.5 rounded">注册</a>
            @endif
            @endauth
        </div>
        <div class="max-w-5xl mx-auto px-4 py-3 flex flex-row items-center justify-between gap-4">
            {{-- Logo 左上角 --}}
            <a href="{{ route('front.home') }}" class="flex items-center gap-2 md:gap-3 shrink-0 min-w-0">
                @if($siteLogo)
                    <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-9 md:h-12 w-auto object-contain shrink-0">
                @endif
                <div class="min-w-0">
                    <span class="text-lg md:text-2xl font-semibold text-[#4a6d63] hover:text-[#3d5b52] transition-colors block truncate">{{ $siteName }}</span>
                    <p class="text-xs md:text-sm text-[#777] hidden sm:block">在喧嚣中寻一方宁静，用文字温暖你我</p>
                </div>
            </a>
            {{-- 主菜单 居中 --}}
            <nav class="hidden md:flex justify-center gap-6 lg:gap-8 flex-wrap items-center flex-1 min-w-0">
                <a href="{{ route('front.home') }}" class="text-[15px] font-medium {{ request()->routeIs('front.home') ? 'text-[#4a6d63]' : 'text-[#6b8e82] hover:text-[#4a6d63]' }} transition-colors">首页</a>
                @foreach($navCategories ?? [] as $navCat)
                @if(!empty($navCat->slug))
                <div class="relative group">
                    <a href="{{ route('front.categories.show', ['category' => $navCat->slug]) }}" class="text-[15px] font-medium {{ request()->routeIs('front.categories.show') && isset($currentCategory) && ($currentCategory->id === $navCat->id || $currentCategory->parent_id === $navCat->id) ? 'text-[#4a6d63]' : 'text-[#6b8e82] hover:text-[#4a6d63]' }} transition-colors inline-flex items-center">
                        {{ $navCat->name }}
                        @if($navCat->children->isNotEmpty())
                        <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        @endif
                    </a>
                    @if($navCat->children->isNotEmpty())
                    <div class="absolute left-1/2 -translate-x-1/2 top-full pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                        <div class="bg-white rounded-lg shadow-lg border border-[#eee] py-2 min-w-[140px]">
                            @foreach($navCat->children as $child)
                            @if(!empty($child->slug))
                            <a href="{{ route('front.categories.show', ['category' => $child->slug]) }}" class="block px-4 py-2 text-sm text-[#666] hover:bg-[#f9f7f5] hover:text-[#4a6d63]">{{ $child->name }}</a>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif
                @endforeach
            </nav>
            <div class="md:hidden shrink-0 ml-auto">
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-[#4a6d63]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
        {{-- 移动端菜单 --}}
        <div class="md:hidden border-t border-[#eee]" x-show="mobileMenuOpen" x-cloak x-transition>
            <div class="px-4 py-4 space-y-1 bg-[#f9f7f5]">
                <a href="{{ route('front.home') }}" class="block py-2 text-[#6b8e82] hover:text-[#4a6d63]">首页</a>
                @foreach($navCategories ?? [] as $navCat)
                @if(!empty($navCat->slug))
                <div>
                    <a href="{{ route('front.categories.show', ['category' => $navCat->slug]) }}" class="block py-2 text-[#6b8e82] hover:text-[#4a6d63] font-medium">{{ $navCat->name }}</a>
                    @if($navCat->children->isNotEmpty())
                    <div class="pl-4 space-y-1">
                        @foreach($navCat->children as $child)
                        @if(!empty($child->slug))
                        <a href="{{ route('front.categories.show', ['category' => $child->slug]) }}" class="block py-1.5 text-sm text-[#777] hover:text-[#4a6d63]">{{ $child->name }}</a>
                        @endif
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif
                @endforeach
                @auth
                <div class="pt-2 mt-2 border-t border-[#eee] flex items-center justify-between">
                    <span class="text-[#777]">{{ Auth::user()->name }}</span>
                    <form action="{{ route('front.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-[#6b8e82] hover:text-[#4a6d63]">退出</button>
                    </form>
                </div>
                @else
                <a href="{{ route('front.login') }}" class="block py-2 text-[#6b8e82] hover:text-[#4a6d63]">登录</a>
                @if(\App\Models\Setting::get('register_enabled', '1') == '1')
                <a href="{{ route('front.register') }}" class="block py-2 text-[#6b8e82] hover:text-[#4a6d63]">注册</a>
                @endif
                @endauth
            </div>
        </div>
    </header>

    @if(session('success'))
    <div class="max-w-5xl mx-auto px-4 py-2">
        <div class="p-3 bg-green-50 text-green-700 rounded-lg text-sm text-center">{{ session('success') }}</div>
    </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- 底部信息 --}}
    <footer class="bg-[#f0eee9] py-10 mt-16 text-center">
        <div class="text-sm text-[#777]">
            {{ \App\Models\Setting::adminName() ?: '小糊涂人生馆' }} © {{ date('Y') }} | 在喧嚣中寻一方宁静，用文字温暖你我
        </div>
        @if($icp = \App\Models\Setting::get('site_icp'))
        <p class="text-xs text-[#999] mt-2">备案号：{{ $icp }}</p>
        @endif
    </footer>

    @stack('scripts')
</body>
</html>
