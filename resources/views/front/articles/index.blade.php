@extends('front.layouts.master')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    {{-- 页面标题与搜索 --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-dark-900 mb-6">文章列表</h1>
        <form method="GET" class="flex flex-wrap gap-3 p-4 bg-white rounded-2xl shadow-sm border border-slate-100">
            <input type="text" name="keyword" value="{{ $keyword ?? '' }}" placeholder="搜索文章..." class="flex-1 min-w-[200px] rounded-xl border border-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
            <select name="category_id" class="rounded-xl border border-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 outline-none bg-white">
                <option value="">全部分类</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="order" class="rounded-xl border border-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 outline-none bg-white">
                <option value="time" {{ request('order') === 'click' ? '' : 'selected' }}>按时间</option>
                <option value="click" {{ request('order') === 'click' ? 'selected' : '' }}>按阅读量</option>
            </select>
            <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors">搜索</button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <div class="space-y-4">
                @forelse($articles as $a)
                <a href="{{ route('front.articles.show', $a) }}" class="flex gap-4 md:gap-6 p-4 md:p-5 bg-white rounded-2xl shadow-sm hover:shadow-lg border border-slate-100 hover:border-primary-100 transition-all duration-300 block group">
                    @if($a->cover_image ?? null)
                    <div class="w-28 h-24 md:w-36 md:h-28 flex-shrink-0 rounded-xl overflow-hidden bg-slate-100">
                        <img data-src="{{ \Illuminate\Support\Facades\Storage::url($a->cover_image) }}" alt="{{ $a->title }}" class="w-full h-full object-cover lazyload group-hover:scale-105 transition-transform duration-300" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h2 class="font-semibold text-lg text-dark-900 group-hover:text-primary-600 transition-colors line-clamp-2">{{ $a->title }}</h2>
                        <p class="text-slate-500 text-sm mt-1">{{ $a->category?->name ?? '未分类' }} · {{ $a->created_at->format('Y-m-d') }} · 阅读 {{ $a->click_num ?? 0 }}</p>
                    </div>
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-primary-500 flex-shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                <div class="py-16 text-center text-slate-500 bg-white rounded-2xl border border-slate-100">暂无文章</div>
                @endforelse
            </div>

            <div class="mt-8">{{ $articles->links() }}</div>
        </div>

        {{-- 侧边栏 --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-24">
                <h3 class="font-bold text-dark-900 mb-4 pb-3 border-b border-slate-100">分类</h3>
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
