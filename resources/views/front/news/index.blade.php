@extends('front.layouts.master')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
    <div class="mb-10">
        <h1 class="text-2xl md:text-3xl font-serif font-semibold text-primary-800 mb-2">新闻资讯</h1>
        <p class="text-dark-800/60 text-sm">最新动态与资讯</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <div class="space-y-4">
                @forelse($news as $n)
                <a href="{{ route('front.news.show', $n) }}" class="flex gap-4 md:gap-6 p-5 bg-white rounded-2xl border border-haze-200 hover:shadow-lg hover:shadow-primary-500/5 hover:border-primary-200 transition-all duration-300 block group">
                    @if($n->cover_image ?? null)
                    <div class="w-28 h-24 md:w-36 md:h-28 flex-shrink-0 rounded-xl overflow-hidden bg-haze-100">
                        <img data-src="{{ \Illuminate\Support\Facades\Storage::url($n->cover_image) }}" alt="{{ $n->title }}" class="w-full h-full object-cover lazyload group-hover:scale-105 transition-transform duration-300" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h2 class="font-serif font-semibold text-lg text-primary-800 group-hover:text-primary-600 transition-colors line-clamp-2">{{ $n->title }}</h2>
                        @if($n->summary)
                        <p class="text-dark-800/60 text-sm mt-1 line-clamp-2">{{ $n->summary }}</p>
                        @endif
                        <p class="text-dark-800/50 text-sm mt-1">{{ ($n->published_at ?? $n->created_at)->format('Y-m-d') }} · 阅读 {{ $n->click_num ?? 0 }}@if($n->source) · {{ $n->source }}@endif</p>
                    </div>
                    <svg class="w-5 h-5 text-haze-400 group-hover:text-primary-500 flex-shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                <div class="py-20 text-center text-dark-800/50 bg-haze-50 rounded-2xl border border-haze-200">暂无新闻资讯</div>
                @endforelse
            </div>

            <div class="mt-8">{{ $news->links() }}</div>
        </div>

        {{-- 侧边栏 --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-haze-200 p-6 sticky top-24 shadow-sm">
                <h3 class="font-serif font-semibold text-primary-800 mb-4 pb-3 border-b border-haze-200">文章分类</h3>
                <ul class="space-y-2">
                    @foreach($categories as $c)
                    <li>
                        <a href="{{ route('front.categories.show', $c) }}" class="flex justify-between py-2 text-dark-800/70 hover:text-primary-600 transition-colors">
                            <span>{{ $c->name }}</span>
                            <span class="text-haze-500 text-sm">({{ $c->articles_count ?? 0 }})</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
