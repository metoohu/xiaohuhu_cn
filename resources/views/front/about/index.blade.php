@extends('front.layouts.master')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
    <div class="text-center mb-12">
        <h1 class="text-2xl md:text-3xl font-serif font-semibold text-primary-800 mb-2">关于我</h1>
        <p class="text-dark-800/60 text-sm">人间清醒，治愈文字</p>
    </div>

    <div class="bg-white rounded-2xl border border-haze-200 p-8 md:p-12 shadow-sm">
        @if($content)
        <div class="prose prose-lg max-w-none prose-p:text-dark-800/80 prose-headings:font-serif prose-headings:text-primary-800 leading-relaxed">
            {!! nl2br(e($content)) !!}
        </div>
        @else
        <p class="text-dark-800/60">暂无介绍内容，敬请期待。</p>
        @endif

        @if($contact)
        <div class="mt-10 pt-8 border-t border-haze-200">
            <h2 class="text-xl font-serif font-semibold text-primary-800 mb-4">联系方式</h2>
            <p class="text-dark-800/60">{{ $contact }}</p>
        </div>
        @endif

        @if($icp)
        <p class="mt-8 text-dark-800/50 text-sm">备案号：{{ $icp }}</p>
        @endif
    </div>
</div>
@endsection
