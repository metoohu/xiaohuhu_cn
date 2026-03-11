@extends('front.layouts.master')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-serif font-semibold text-primary-800 mb-2">{{ $category->name }}</h1>
                @if($category->description)
                <p class="text-dark-800/70">{{ $category->description }}</p>
                @endif

                @if($category->children->isNotEmpty())
                <div class="flex flex-wrap gap-2 mt-4">
                    <span class="text-dark-800/50 text-sm">子分类：</span>
                    @foreach($category->children as $child)
                    <a href="{{ route('front.categories.show', $child) }}" class="px-3 py-1.5 bg-haze-100 rounded-full text-sm hover:bg-primary-100 hover:text-primary-700 transition-colors">{{ $child->name }}</a>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="space-y-4">
                @forelse($articles as $a)
                <a href="{{ route('front.articles.show', $a) }}" class="flex gap-4 md:gap-6 p-5 bg-white rounded-2xl border border-haze-200 hover:shadow-lg hover:shadow-primary-500/5 hover:border-primary-200 transition-all duration-300 block group">
                    @if($a->cover_image ?? null)
                    <div class="w-28 h-24 md:w-36 md:h-28 flex-shrink-0 rounded-xl overflow-hidden bg-haze-100">
                        <img data-src="{{ \Illuminate\Support\Facades\Storage::url($a->cover_image) }}" alt="{{ $a->title }}" class="w-full h-full object-cover lazyload group-hover:scale-105 transition-transform duration-300" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h2 class="font-serif font-semibold text-lg text-primary-800 group-hover:text-primary-600 transition-colors line-clamp-2">{{ $a->title }}</h2>
                        <p class="text-dark-800/60 text-sm mt-1">{{ $a->created_at->format('Y-m-d') }} · 阅读 {{ $a->click_num ?? 0 }}</p>
                    </div>
                    <svg class="w-5 h-5 text-haze-400 group-hover:text-primary-500 flex-shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                <div class="py-20 text-center text-dark-800/50 bg-haze-50 rounded-2xl border border-haze-200">该分类暂无文章</div>
                @endforelse
            </div>

            <div class="mt-8">{{ $articles->links() }}</div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-haze-200 p-6 mb-6 sticky top-24 shadow-sm">
                <h3 class="font-serif font-semibold text-primary-800 mb-4 pb-3 border-b border-haze-200">分类</h3>
                <ul class="space-y-2">
                    @foreach($categories as $c)
                    <li>
                        <a href="{{ route('front.categories.show', $c) }}" class="flex justify-between py-2 transition-colors {{ $c->id == $category->id ? 'text-primary-600 font-medium' : 'text-dark-800/70 hover:text-primary-600' }}">
                            <span>{{ $c->name }}</span>
                            <span class="text-haze-500 text-sm">({{ $c->articles_count ?? 0 }})</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white rounded-2xl border border-haze-200 p-6 shadow-sm">
                <h3 class="font-serif font-semibold text-primary-800 mb-4 pb-3 border-b border-haze-200">热门文章</h3>
                <ul class="space-y-3">
                    @foreach($hotArticles as $a)
                    <li>
                        <a href="{{ route('front.articles.show', $a) }}" class="text-dark-800/70 hover:text-primary-600 line-clamp-2 transition-colors">{{ $a->title }}</a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
