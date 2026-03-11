@extends('front.layouts.master')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="text-center">
        <div class="text-8xl font-serif font-semibold text-primary-200 mb-4">404</div>
        <h1 class="text-2xl font-serif font-semibold text-primary-800 mb-2">页面未找到</h1>
        <p class="text-dark-800/60 mb-8">抱歉，您访问的页面不存在或已被移除</p>
        <a href="{{ route('front.home') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 text-white rounded-full hover:bg-primary-600 font-medium transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            返回首页
        </a>
    </div>
</div>
@endsection
