@extends('front.layouts.master')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-dark-900 mb-2">新闻资讯</h1>
        <p class="text-slate-600">最新资讯与动态</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <div class="space-y-4">
                @forelse($news as $n)
                <a href="{{ route('front.news.show', $n) }}" class="flex gap-4 md:gap-6 p-4 md:p-5 bg-white rounded-2xl shadow-sm hover:shadow-lg border border-slate-100 hover:border-primary-100 transition-all duration-300 block group">
                    @if($n->cover_image ?? null)
                    <div class="w-28 h-24 md:w-36 md:h-28 flex-shrink-0 rounded-xl overflow-hidden bg-slate-100">
                        <img data-src="{{ \Illuminate\Support\Facades\Storage::url($n->cover_image) }}" alt="{{ $n->title }}" class="w-full h-full object-cover lazyload group-hover:scale-105 transition-transform duration-300" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h2 class="font-semibold text-lg text-dark-900 group-hover:text-primary-600 transition-colors line-clamp-2">{{ $n->title }}</h2>
                        @if($n->summary)
                        <p class="text-slate-500 text-sm mt-1 line-clamp-2">{{ $n->summary }}</p>
                        @endif
                        <p class="text-slate-500 text-sm mt-1">{{ ($n->published_at ?? $n->created_at)->format('Y-m-d') }} · 阅读 {{ $n->click_num ?? 0 }}@if($n->source) · {{ $n->source }}@endif</p>
                    </div>
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-primary-500 flex-shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                <div class="py-16 text-center text-slate-500 bg-white rounded-2xl border border-slate-100">暂无新闻资讯</div>
                @endforelse
            </div>

            <div class="mt-8">{{ $news->links() }}</div>
        </div>

        {{-- 侧边栏 --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-24">
                <h3 class="font-bold text-dark-900 mb-4 pb-3 border-b border-slate-100">文章分类</h3>
                <ul class="space-y-2">
                    @foreach($categories as $c)
                    <li>
                        <a href="{{ route('front.categories.show', $c) }}" class="flex justify-between py-2 text-slate-600 hover:text-primary-600 transition-colors">
                            <span>{{ $c->name }}</span>
                            <span class="text-slate-400 text-sm">({{ $c->articles_count ?? 0 }})</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
