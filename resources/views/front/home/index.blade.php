@extends('front.layouts.master')

@section('content')
{{-- Hero 区 --}}
<section class="bg-gradient-to-b from-blue-50 to-white py-16 md:py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">学习・分享・成长</h1>
        <p class="text-slate-600 text-base md:text-lg max-w-2xl mx-auto mb-8">
            {{ \App\Models\Setting::adminName() ?: '心灵归宿' }}的学习笔记与技术分享，记录编程路上的点点滴滴，与你一起探索技术的奥秘。

        </p>
        <a href="{{ route('front.articles.index') }}" class="inline-block px-8 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">开始探索</a>
    </div>
</section>

{{-- 精选文章 --}}
<section class="py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2">精选文章</h2>
            <p class="text-slate-300 text-sm">最新发布的技术笔记与学习心得</p>
        </div>

        @php $articles = $recommend_articles->isNotEmpty() ? $recommend_articles : $latest_articles->take(6); @endphp
        @if($articles->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($articles as $a)
            <a href="{{ route('front.articles.show', $a) }}" class="group bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-lg hover:border-primary-100 transition-all duration-300">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100 relative">
                    @if($a->cover_image)
                    <img data-src="{{ \Illuminate\Support\Facades\Storage::url($a->cover_image) }}" alt="{{ $a->title }}" class="w-full h-full object-cover lazyload group-hover:scale-105 transition-transform duration-500" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                    </div>
                    @endif
                    @if($a->category)
                    <span class="absolute top-3 left-3 px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-medium rounded">{{ $a->category->name }}</span>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="font-semibold text-lg text-slate-800 group-hover:text-primary-600 transition-colors line-clamp-2">{{ $a->title }}</h3>
                    <p class="text-slate-500 text-sm mt-2 line-clamp-2">{{ Str::limit(strip_tags($a->content ?? ''), 80) ?: '点击阅读全文' }}</p>
                    <div class="mt-4 flex justify-between items-center">
                        <span class="text-slate-400 text-xs">{{ $a->created_at->format('Y-m-d') }}</span>
                        <span class="text-primary-600 text-sm font-medium group-hover:underline flex items-center gap-1">
                            阅读更多
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('front.articles.index') }}" class="text-primary-600 hover:text-primary-700 font-medium">查看全部文章 →</a>
        </div>
        @else
        <div class="text-center py-16 text-slate-500">暂无文章</div>
        @endif
    </div>
</section>

{{-- 文章分类 --}}
@if($categories->isNotEmpty())
<section class="py-16 md:py-20 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2">文章分类</h2>
            <p class="text-slate-300 text-sm">持续更新中，点击查看更多</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($categories as $cat)
            <a href="{{ route('front.categories.show', $cat) }}" class="group bg-white rounded-xl border border-slate-200 p-6 hover:border-primary-200 hover:shadow-md transition-all duration-200">
                <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center mx-auto mb-4 group-hover:bg-primary-200 transition-colors overflow-hidden">
                    @if($cat->icon)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($cat->icon) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                    @else
                    <span class="text-primary-600 text-lg font-mono">&lt;/&gt;</span>
                    @endif
                </div>
                <h3 class="font-semibold text-slate-800 text-center mb-2">{{ $cat->name }}</h3>
                <p class="text-slate-500 text-sm text-center mb-3 line-clamp-2">{{ $cat->description ?: '技术文章与学习笔记' }}</p>
                <p class="text-primary-600 text-sm text-center font-medium">{{ $cat->articles_count ?? 0 }} 篇文章</p>
            </a>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('front.categories.index') }}" class="text-primary-600 hover:text-primary-700 font-medium">查看全部分类 →</a>
        </div>
    </div>
</section>
@endif

{{-- 关于我 --}}
<section class="py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2">关于我</h2>
            <p class="text-slate-300 text-sm">一个热爱学习与分享的开发者</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-8 md:p-12 flex flex-col md:flex-row gap-8 items-center">
            <div class="flex-shrink-0">
                <div class="w-24 h-24 md:w-32 md:h-32 rounded-full bg-primary-100 flex items-center justify-center overflow-hidden">
                    @if($avatar = \App\Models\Setting::get('site_logo'))
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($avatar) }}" alt="" class="w-full h-full object-cover">
                    @else
                    <span class="text-4xl font-bold text-primary-600">{{ mb_substr(\App\Models\Setting::adminName() ?: '心', 0, 1) }}</span>
                    @endif
                </div>
            </div>
            <div class="flex-1 text-center md:text-left">
                <h3 class="text-xl font-bold text-slate-800 mb-3">{{ \App\Models\Setting::adminName() ?: '心灵归宿' }}</h3>
                <p class="text-slate-600 leading-relaxed mb-6">
                    {{ \App\Models\Setting::get('about_content') ?: '热爱编程与技术的开发者，记录学习与成长的点滴，分享实用的技术心得与经验。' }}
                </p>
                <div class="flex flex-wrap gap-4 justify-center md:justify-start mb-6">
                    <span class="px-4 py-2 bg-slate-100 rounded-lg text-sm text-slate-600">{{ \App\Models\Article::where('status', 'published')->count() }}+ 文章数量</span>
                    <span class="px-4 py-2 bg-slate-100 rounded-lg text-sm text-slate-600">{{ $categories->count() }} 技术栈</span>
                    <span class="px-4 py-2 bg-slate-100 rounded-lg text-sm text-slate-600">∞ 学习热情</span>
                </div>
                <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                    @foreach($categories->take(5) as $c)
                    <span class="px-3 py-1.5 bg-primary-50 text-primary-700 rounded-full text-sm">{{ $c->name }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
