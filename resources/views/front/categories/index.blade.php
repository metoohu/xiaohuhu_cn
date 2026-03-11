@extends('front.layouts.master')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
    <div class="text-center mb-12">
        <h1 class="text-2xl md:text-3xl font-serif font-semibold text-primary-800 mb-2">文章分类</h1>
        <p class="text-dark-800/60 text-sm">按主题浏览，找到你需要的治愈</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($categories as $cat)
        <a href="{{ route('front.categories.show', $cat) }}" class="group bg-white rounded-2xl border border-haze-200 p-6 hover:border-primary-200 hover:shadow-lg hover:shadow-primary-500/5 transition-all duration-300">
            <div class="w-14 h-14 rounded-xl bg-haze-100 flex items-center justify-center mx-auto mb-4 group-hover:bg-primary-100 transition-colors overflow-hidden">
                @if($cat->icon)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($cat->icon) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                @else
                <span class="text-2xl">📖</span>
                @endif
            </div>
            <h3 class="font-serif font-semibold text-primary-800 text-center mb-2">{{ $cat->name }}</h3>
            <p class="text-dark-800/60 text-sm text-center mb-3 line-clamp-2">{{ $cat->description ?: '生活感悟与治愈文字' }}</p>
            <p class="text-primary-500 text-sm text-center font-medium">{{ $cat->articles_count ?? 0 }} 篇文章</p>
        </a>
        @endforeach
    </div>

    @if($categories->isEmpty())
    <div class="text-center py-20 text-dark-800/50 bg-haze-50 rounded-2xl border border-haze-200">暂无分类</div>
    @endif
</div>
@endsection
