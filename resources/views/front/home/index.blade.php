@extends('front.layouts.master')

@section('content')
{{-- 核心 Banner 区 --}}
@php
    $bannerList = $banners ?? [];
    $bannerUrls = [];
    foreach ($bannerList as $item) {
        $url = is_array($item) ? ($item['url'] ?? $item['path'] ?? null) : $item;
        if ($url && is_string($url)) {
            $fullUrl = str_starts_with($url, 'http') ? $url : \Illuminate\Support\Facades\Storage::url($url);
            $bannerUrls[] = $fullUrl;
        }
    }
    $hasBanners = count($bannerUrls) > 0;
    $fallbackGradient = 'linear-gradient(135deg, #6b8e82 0%, #8fa99e 50%, #a8c9bc 100%)';
@endphp
<section class="relative w-full aspect-[16/9] sm:aspect-[3/1] md:aspect-[4/1] min-h-[140px] sm:min-h-[160px] overflow-hidden flex items-center justify-center shadow-[0_8px_30px_rgba(0,0,0,0.12)]" x-data="{ current: 0, total: {{ count($bannerUrls) ?: 1 }} }" x-init="@if(count($bannerUrls) > 1) setInterval(() => { current = (current + 1) % total }, 5000) @endif">
    {{-- 多圖輪播背景 --}}
    @if($hasBanners)
        @foreach($bannerUrls as $i => $url)
        <div class="absolute inset-0 bg-cover bg-center transition-opacity duration-700" style="background-image: url('{{ e($url) }}');" x-show="current === {{ $i }}" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak></div>
        @endforeach
    @else
        <div class="absolute inset-0" style="background: {{ $fallbackGradient }};"></div>
    @endif
    {{-- 漸層遮罩：底部加深提升文字可讀性 --}}
    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
    {{-- 頂部微光暈 --}}
    <div class="absolute inset-0 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
    {{-- 輪播指示點（多圖時顯示） --}}
    @if(count($bannerUrls) > 1)
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
        @foreach($bannerUrls as $i => $_)
        <button type="button" class="w-2 h-2 rounded-full transition-all duration-300" :class="current === {{ $i }} ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80'" @click="current = {{ $i }}" aria-label="切換至第 {{ $i + 1 }} 張"></button>
        @endforeach
    </div>
    @endif
    <div class="relative z-10 text-white text-center px-6 max-w-[800px]">
        <p class="text-base md:text-lg leading-relaxed opacity-95 drop-shadow-md" style="text-shadow: 0 1px 3px rgba(0,0,0,0.5);">人间清醒，治愈文字。<br>在喧嚣中寻一方宁静，用文字温暖你我。</p>
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
