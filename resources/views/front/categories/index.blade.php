@extends('front.layouts.master')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-slate-800 mb-2">文章分类</h1>
        <p class="text-slate-400 text-sm">持续更新中，点击查看更多</p>
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

    @if($categories->isEmpty())
    <div class="text-center py-16 text-slate-500">暂无分类</div>
    @endif
</div>
@endsection
