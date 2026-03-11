@extends('front.layouts.master')

@section('content')
{{-- Hero 区：人间清醒、治愈文字 --}}
<section class="relative py-20 md:py-28 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-haze-100 via-haze-50/80 to-transparent"></div>
    <div class="absolute top-20 right-10 w-72 h-72 bg-primary-200/30 rounded-full blur-3xl"></div>
    <div class="absolute bottom-10 left-10 w-48 h-48 bg-haze-300/40 rounded-full blur-2xl"></div>
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-primary-600 text-sm font-medium tracking-widest mb-4">人间清醒 · 治愈文字</p>
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-serif font-semibold text-primary-800 mb-6 leading-tight">
            小糊涂人生馆
        </h1>
        <p class="text-dark-800/80 text-base md:text-lg max-w-2xl mx-auto leading-relaxed mb-10">
            在喧嚣中寻一方宁静，用文字温暖你我。记录生活感悟，分享人间清醒，与你一起在平凡日子里找到治愈的力量。
        </p>
        <a href="{{ route('front.articles.index') }}" class="inline-flex items-center gap-2 px-8 py-3.5 bg-primary-500 text-white font-medium rounded-full hover:bg-primary-600 transition-all duration-300 shadow-lg shadow-primary-500/25 hover:shadow-primary-500/35">
            开始阅读
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>

{{-- 精选文章 --}}
<section class="py-16 md:py-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-10">
            <div>
                <h2 class="text-2xl md:text-3xl font-serif font-semibold text-primary-800 mb-1">精选文章</h2>
                <p class="text-dark-800/60 text-sm">治愈文字，温暖人心</p>
            </div>
            <a href="{{ route('front.articles.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium hidden sm:block">查看全部 →</a>
        </div>

        @php $articles = $recommend_articles->isNotEmpty() ? $recommend_articles : $latest_articles->take(6); @endphp
        @if($articles->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($articles as $a)
            <a href="{{ route('front.articles.show', $a) }}" class="group bg-white rounded-2xl border border-haze-200 overflow-hidden hover:shadow-xl hover:shadow-primary-500/10 hover:border-primary-200 transition-all duration-300">
                <div class="aspect-[4/3] overflow-hidden bg-haze-100 relative">
                    @if($a->cover_image)
                    <img data-src="{{ \Illuminate\Support\Facades\Storage::url($a->cover_image) }}" alt="{{ $a->title }}" class="w-full h-full object-cover lazyload group-hover:scale-105 transition-transform duration-500" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-haze-400">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    @endif
                    @if($a->category)
                    <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/90 text-primary-600 text-xs font-medium rounded-full backdrop-blur-sm">{{ $a->category->name }}</span>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="font-serif font-semibold text-lg text-primary-800 group-hover:text-primary-600 transition-colors line-clamp-2">{{ $a->title }}</h3>
                    <p class="text-dark-800/60 text-sm mt-2 line-clamp-2">{{ Str::limit(strip_tags($a->content ?? ''), 70) ?: '点击阅读全文' }}</p>
                    <div class="mt-4 flex justify-between items-center">
                        <span class="text-dark-800/50 text-xs">{{ $a->created_at->format('Y-m-d') }}</span>
                        <span class="text-primary-500 text-sm font-medium group-hover:underline flex items-center gap-1">
                            阅读
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        <div class="text-center mt-10 sm:hidden">
            <a href="{{ route('front.articles.index') }}" class="text-primary-600 hover:text-primary-700 font-medium">查看全部文章 →</a>
        </div>
        @else
        <div class="text-center py-20 text-dark-800/50 bg-haze-50 rounded-2xl border border-haze-200">暂无文章，敬请期待</div>
        @endif
    </div>
</section>

{{-- 文章分类 --}}
@if($categories->isNotEmpty())
<section class="py-16 md:py-20 bg-white/60">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-10">
            <div>
                <h2 class="text-2xl md:text-3xl font-serif font-semibold text-primary-800 mb-1">文章分类</h2>
                <p class="text-dark-800/60 text-sm">按主题浏览，找到你需要的治愈</p>
            </div>
            <a href="{{ route('front.categories.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium hidden sm:block">查看全部分类 →</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($categories as $cat)
            <a href="{{ route('front.categories.show', $cat) }}" class="group bg-white rounded-2xl border border-haze-200 p-6 hover:border-primary-200 hover:shadow-lg hover:shadow-primary-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-haze-100 flex items-center justify-center mx-auto mb-4 group-hover:bg-primary-100 transition-colors overflow-hidden">
                    @if($cat->icon)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($cat->icon) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                    @else
                    <span class="text-primary-500 text-xl">📖</span>
                    @endif
                </div>
                <h3 class="font-serif font-semibold text-primary-800 text-center mb-2">{{ $cat->name }}</h3>
                <p class="text-dark-800/60 text-sm text-center mb-3 line-clamp-2">{{ $cat->description ?: '生活感悟与治愈文字' }}</p>
                <p class="text-primary-500 text-sm text-center font-medium">{{ $cat->articles_count ?? 0 }} 篇</p>
            </a>
            @endforeach
        </div>
        <div class="text-center mt-10 sm:hidden">
            <a href="{{ route('front.categories.index') }}" class="text-primary-600 hover:text-primary-700 font-medium">查看全部分类 →</a>
        </div>
    </div>
</section>
@endif

{{-- 关于我 --}}
<section class="py-16 md:py-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-serif font-semibold text-primary-800 mb-1">关于小糊涂</h2>
            <p class="text-dark-800/60 text-sm">在平凡日子里，记录人间清醒</p>
        </div>

        <div class="bg-white rounded-2xl border border-haze-200 p-8 md:p-12 flex flex-col md:flex-row gap-8 items-center shadow-sm shadow-haze-100/50">
            <div class="flex-shrink-0">
                <div class="w-24 h-24 md:w-28 md:h-28 rounded-2xl bg-haze-100 flex items-center justify-center overflow-hidden border border-haze-200">
                    @if($avatar = \App\Models\Setting::get('site_logo'))
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($avatar) }}" alt="" class="w-full h-full object-cover">
                    @else
                    <span class="text-4xl font-serif font-semibold text-primary-500">{{ mb_substr(\App\Models\Setting::adminName() ?: '糊', 0, 1) }}</span>
                    @endif
                </div>
            </div>
            <div class="flex-1 text-center md:text-left">
                <h3 class="text-xl font-serif font-semibold text-primary-800 mb-3">{{ \App\Models\Setting::adminName() ?: '小糊涂人生馆' }}</h3>
                <p class="text-dark-800/80 leading-relaxed mb-6">
                    {{ \App\Models\Setting::get('about_content') ?: '人间清醒，治愈文字。在喧嚣中寻一方宁静，用文字记录生活感悟，与你分享平凡日子里的温暖与力量。' }}
                </p>
                <div class="flex flex-wrap gap-3 justify-center md:justify-start mb-6">
                    <span class="px-4 py-2 bg-haze-100 text-primary-700 rounded-full text-sm">{{ \App\Models\Article::where('status', 'published')->count() }}+ 篇文章</span>
                    <span class="px-4 py-2 bg-haze-100 text-primary-700 rounded-full text-sm">{{ $categories->count() }} 个分类</span>
                </div>
                <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                    @foreach($categories->take(5) as $c)
                    <a href="{{ route('front.categories.show', $c) }}" class="px-3 py-1.5 bg-primary-50 text-primary-600 rounded-full text-sm hover:bg-primary-100 transition-colors">{{ $c->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
