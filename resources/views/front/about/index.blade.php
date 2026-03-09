@extends('front.layouts.master')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
    <div class="text-center mb-12">
        <h1 class="text-3xl md:text-4xl font-bold text-slate-800 mb-2">关于我</h1>
        <p class="text-slate-400 text-sm">一个热爱学习与分享的开发者</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8 md:p-12">
        @if($content)
        <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed">
            {!! nl2br(e($content)) !!}
        </div>
        @else
        <p class="text-slate-500">暂无介绍内容，敬请期待。</p>
        @endif

        @if($contact)
        <div class="mt-10 pt-8 border-t border-slate-200">
            <h2 class="text-xl font-bold text-slate-800 mb-4">联系方式</h2>
            <p class="text-slate-600">{{ $contact }}</p>
        </div>
        @endif

        @if($icp)
        <p class="mt-8 text-slate-400 text-sm">备案号：{{ $icp }}</p>
        @endif
    </div>
</div>
@endsection
