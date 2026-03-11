@extends('front.layouts.master')

@section('content')
{{-- 核心 Banner 区 --}}
@php
    $banners = \App\Models\Setting::get('banner_images');
    $banners = $banners ? json_decode($banners, true) : [];
    $bannerUrl = null;
    if (!empty($banners[0])) {
        $first = $banners[0];
        $url = $first['url'] ?? $first['path'] ?? (is_string($first) ? $first : null);
        if ($url) {
            $bannerUrl = str_starts_with($url, 'http') ? $url : \Illuminate\Support\Facades\Storage::url($url);
        }
    }
@endphp
<section class="h-[400px] bg-cover bg-center flex items-center justify-center text-white text-center px-5" style="background: {{ $bannerUrl ? "linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('" . e($bannerUrl) . "')" : 'linear-gradient(135deg, #6b8e82 0%, #8fa99e 50%, #a8c9bc 100%)' }}; background-size: cover; background-position: center;">
    <div class="max-w-[800px]">
        <h1 class="text-3xl md:text-4xl font-semibold mb-5">小糊涂人生馆</h1>
        <p class="text-lg leading-relaxed">人间清醒，治愈文字。<br>在喧嚣中寻一方宁静，用文字温暖你我。</p>
    </div>
</section>

{{-- 精选内容区 --}}
<section class="max-w-[1200px] mx-auto px-5 my-16">
    @php
        $fallback = $recommend_articles->isNotEmpty() ? $recommend_articles : $latest_articles;
        $awakeArticles = $awake_articles->isNotEmpty() ? $awake_articles : $fallback->take(3);
        $healingArticles = $healing_articles->isNotEmpty() ? $healing_articles : $fallback->skip(3)->take(3);
    @endphp

    {{-- 人间清醒板块 --}}
    <h2 class="text-2xl text-[#4a6d63] text-center mb-10 relative pb-2">
        人间清醒
        <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[60px] h-0.5 bg-[#6b8e82]"></span>
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-5">
        @forelse($awakeArticles as $a)
        <a href="{{ route('front.articles.show', $a) }}" class="group bg-white p-6 rounded-lg shadow-[0_2px_10px_rgba(0,0,0,0.05)] hover:-translate-y-1 transition-transform duration-300">
            <h3 class="text-lg text-[#333] mb-4 group-hover:text-[#4a6d63] transition-colors line-clamp-2">{{ $a->title }}</h3>
            <p class="text-sm text-[#666] mb-5 line-clamp-2">{{ Str::limit(strip_tags($a->content ?? ''), 80) ?: '点击阅读全文' }}</p>
            <div class="flex justify-between items-center">
                <span class="text-sm text-[#6b8e82] group-hover:text-[#4a6d63]">阅读更多 →</span>
                <span class="text-xs text-[#999]">阅读 {{ $a->click_num ?? 0 }}</span>
            </div>
        </a>
        @empty
        <div class="col-span-3 py-12 text-center text-[#777]">暂无文章，敬请期待</div>
        @endforelse
    </div>

    {{-- 治愈文字板块 --}}
    <h2 class="text-2xl text-[#4a6d63] text-center mb-10 mt-20 relative pb-2">
        治愈文字
        <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[60px] h-0.5 bg-[#6b8e82]"></span>
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-5">
        @forelse($healingArticles as $a)
        <a href="{{ route('front.articles.show', $a) }}" class="group bg-white p-6 rounded-lg shadow-[0_2px_10px_rgba(0,0,0,0.05)] hover:-translate-y-1 transition-transform duration-300">
            <h3 class="text-lg text-[#333] mb-4 group-hover:text-[#4a6d63] transition-colors line-clamp-2">{{ $a->title }}</h3>
            <p class="text-sm text-[#666] mb-5 line-clamp-2">{{ Str::limit(strip_tags($a->content ?? ''), 80) ?: '点击阅读全文' }}</p>
            <div class="flex justify-between items-center">
                <span class="text-sm text-[#6b8e82] group-hover:text-[#4a6d63]">阅读更多 →</span>
                <span class="text-xs text-[#999]">阅读 {{ $a->click_num ?? 0 }}</span>
            </div>
        </a>
        @empty
        @if($awakeArticles->isEmpty())
        <div class="col-span-3 py-12 text-center text-[#777]">暂无文章，敬请期待</div>
        @else
        <div class="col-span-3 py-8 text-center">
            <a href="{{ route('front.articles.index') }}" class="text-[#6b8e82] hover:text-[#4a6d63] font-medium">查看全部文章 →</a>
        </div>
        @endif
        @endforelse
    </div>

    @if($awakeArticles->isNotEmpty() || $healingArticles->isNotEmpty())
    <div class="text-center mt-10">
        <a href="{{ route('front.articles.index') }}" class="text-[#6b8e82] hover:text-[#4a6d63] font-medium">查看全部文章 →</a>
    </div>
    @endif
</section>
@endsection
