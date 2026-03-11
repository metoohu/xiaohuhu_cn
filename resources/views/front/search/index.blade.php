@extends('front.layouts.master')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <h1 class="text-2xl md:text-3xl font-serif font-semibold text-primary-800 mb-6">搜索{{ $keyword ? "：{$keyword}" : '' }}</h1>

            <form method="GET" action="{{ route('front.search') }}" class="mb-8">
                <div class="flex gap-3 p-4 bg-white rounded-2xl border border-haze-200 shadow-sm">
                    <input type="text" name="q" value="{{ $keyword }}" placeholder="输入关键词搜索..." class="flex-1 rounded-xl border border-haze-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500/50 focus:border-primary-400 outline-none transition bg-haze-50/50">
                    <button type="submit" class="px-6 py-2.5 bg-primary-500 text-white rounded-xl hover:bg-primary-600 font-medium transition-colors">搜索</button>
                </div>
            </form>

            <div class="space-y-4">
                @forelse($articles as $a)
                <a href="{{ route('front.articles.show', $a) }}" class="flex gap-4 md:gap-6 p-5 bg-white rounded-2xl border border-haze-200 hover:shadow-lg hover:shadow-primary-500/5 hover:border-primary-200 transition-all duration-300 block group">
                    @if($a->cover_image ?? null)
                    <div class="w-28 h-24 md:w-36 md:h-28 flex-shrink-0 rounded-xl overflow-hidden bg-haze-100">
                        <img data-src="{{ \Illuminate\Support\Facades\Storage::url($a->cover_image) }}" alt="{{ $a->title }}" class="w-full h-full object-cover lazyload group-hover:scale-105 transition-transform duration-300" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h2 class="font-serif font-semibold text-lg text-primary-800 group-hover:text-primary-600 transition-colors line-clamp-2">{!! $keyword ? preg_replace('/(' . preg_quote($keyword, '/') . ')/iu', '<mark class="bg-primary-200 text-primary-800 rounded px-0.5">$1</mark>', e($a->title)) : e($a->title) !!}</h2>
                        <p class="text-dark-800/60 text-sm mt-1">{{ $a->category?->name ?? '未分类' }} · {{ $a->created_at->format('Y-m-d') }}</p>
                    </div>
                    <svg class="w-5 h-5 text-haze-400 group-hover:text-primary-500 flex-shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                <div class="py-20 text-center text-dark-800/50 bg-haze-50 rounded-2xl border border-haze-200">
                    {{ $keyword ? '未找到相关文章，换个关键词试试吧' : '请输入关键词进行搜索' }}
                </div>
                @endforelse
            </div>

            @if($articles->hasPages())
            <div class="mt-8">{{ $articles->appends(['q' => $keyword])->links() }}</div>
            @endif
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-haze-200 p-6 sticky top-24 shadow-sm">
                <h3 class="font-serif font-semibold text-primary-800 mb-4 pb-3 border-b border-haze-200">分类</h3>
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
