@extends('front.layouts.master')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <article class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 md:p-8">
                    <h1 class="text-2xl md:text-3xl font-bold text-dark-900 mb-4">{{ $news->title }}</h1>
                    <div class="flex flex-wrap gap-4 text-slate-500 text-sm mb-6">
                        <span>{{ ($news->published_at ?? $news->created_at)->format('Y-m-d H:i') }}</span>
                        <span>阅读 {{ $news->click_num }}</span>
                        @if($news->source)
                        <span>来源：{{ $news->source }}</span>
                        @endif
                    </div>
                    @if($news->cover_image)
                    <div class="rounded-xl overflow-hidden mb-8 bg-slate-100">
                        <img data-src="{{ \Illuminate\Support\Facades\Storage::url($news->cover_image) }}" alt="{{ $news->title }}" class="w-full lazyload" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                    </div>
                    @endif
                    <div class="prose prose-slate max-w-none prose-headings:text-dark-900 prose-a:text-primary-600 prose-img:rounded-lg mb-8">{!! $news->content !!}</div>

                    <div class="flex justify-between pt-6 border-t border-slate-100 text-sm">
                        <div>
                            @if($prevNews)
                            <a href="{{ route('front.news.show', $prevNews) }}" class="text-primary-600 hover:text-primary-700 font-medium">← {{ Str::limit($prevNews->title, 28) }}</a>
                            @else
                            <span class="text-slate-400">没有上一篇了</span>
                            @endif
                        </div>
                        <div class="text-right">
                            @if($nextNews)
                            <a href="{{ route('front.news.show', $nextNews) }}" class="text-primary-600 hover:text-primary-700 font-medium">{{ Str::limit($nextNews->title, 28) }} →</a>
                            @else
                            <span class="text-slate-400">没有下一篇了</span>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        </div>

        {{-- 側邊欄 --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6 sticky top-24">
                <h3 class="font-bold text-dark-900 mb-4 pb-3 border-b border-slate-100">文章分类</h3>
                <ul class="space-y-2">
                    @foreach($categories as $c)
                    <li>
                        <a href="{{ route('front.categories.show', $c) }}" class="flex justify-between py-2 text-slate-600 hover:text-primary-600 transition-colors">
                            <span>{{ $c->name }}</span>
                            <span class="text-slate-400 text-sm">({{ $c->articles_count }})</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-bold text-dark-900 mb-4 pb-3 border-b border-slate-100">热门资讯</h3>
                <ul class="space-y-3">
                    @foreach($hotNews as $n)
                    <li>
                        <a href="{{ route('front.news.show', $n) }}" class="text-slate-600 hover:text-primary-600 line-clamp-2 transition-colors">{{ $n->title }}</a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
