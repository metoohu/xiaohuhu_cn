@extends('front.layouts.master')

@section('content')
@php
    $seo = [
        'title' => '新建投稿 - ' . \App\Models\Setting::get('site_title', \App\Models\Setting::adminName()),
        'keywords' => \App\Models\Setting::seoKeywords(),
        'description' => \App\Models\Setting::seoDescription(),
    ];
@endphp
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-serif font-semibold text-primary-800 mb-6">新建投稿</h1>

    @if($categories->isEmpty())
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-900 mb-6">
        暂无可选分类：请管理员在后台为分类勾选「允许用户投稿」并启用分类。
    </div>
    @endif

    <form id="user-article-form" method="POST" action="{{ route('front.my.articles.store') }}" class="space-y-5 bg-white rounded-2xl border border-haze-200 p-6 shadow-sm">
        @csrf
        <div>
            <label class="block text-sm font-medium text-dark-800/80 mb-1">分类 <span class="text-red-500">*</span></label>
            @if($categories->isEmpty())
            {{-- 勿用 disabled 的 select：多数浏览器点击无展开，易被误认为「坏了」 --}}
            <div class="w-full rounded-xl border border-haze-200 bg-haze-50 px-3 py-2.5 text-sm text-dark-800/80">
                当前没有「允许用户投稿」的启用分类，无法新建稿件。请管理员在后台 <strong>分类管理</strong> 中勾选「允许用户投稿」并启用分类。
            </div>
            @else
            <select name="category_id" required class="w-full rounded-xl border-haze-200">
                <option value="">请选择</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @endif
            @error('category_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-dark-800/80 mb-1">标题 <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="120" class="w-full rounded-xl border-haze-200">
            @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-dark-800/80 mb-1">正文 <span class="text-red-500">*</span></label>
            <p class="text-xs text-dark-800/50 mb-1">与后台文章相同的可视化编辑器；字数按纯文本计算。</p>
            {{-- 勿加 required：TinyMCE 会隐藏 textarea，浏览器会认为正文为空而静默拦截提交 --}}
            <textarea id="user-article-content" name="content" rows="14" class="w-full rounded-xl border-haze-200 font-sans text-sm">{{ old('content') }}</textarea>
            <p id="user-article-content-client-error" class="text-red-600 text-sm mt-1 hidden" role="alert"></p>
            @error('content')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-dark-800/80 mb-1">标签（最多 3 个，逗号分隔）</label>
            <input type="text" name="tags_csv" value="{{ old('tags_csv') }}" class="w-full rounded-xl border-haze-200" placeholder="例如：生活,感悟">
            @error('tags')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" name="action" value="draft" class="px-5 py-2.5 rounded-xl bg-haze-200 text-dark-800 hover:bg-haze-300 text-sm" {{ $categories->isEmpty() ? 'disabled' : '' }}>保存草稿</button>
            <button type="submit" name="action" value="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 text-white hover:bg-primary-700 text-sm" {{ $categories->isEmpty() ? 'disabled' : '' }}>保存并提交审核</button>
            <a href="{{ route('front.my.profile', ['page' => 'articles']) }}" class="px-5 py-2.5 text-sm text-primary-600 hover:underline">返回列表</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('front.my.articles.partials.tinymce', ['formId' => 'user-article-form', 'textareaId' => 'user-article-content'])
@endpush
