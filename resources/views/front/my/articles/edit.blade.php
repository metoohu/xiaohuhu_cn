@extends('front.layouts.master')

@section('content')
@php
    $seo = [
        'title' => '编辑投稿 - ' . \App\Models\Setting::get('site_title', \App\Models\Setting::adminName()),
        'keywords' => \App\Models\Setting::seoKeywords(),
        'description' => \App\Models\Setting::seoDescription(),
    ];
    $defaultBody = $userArticle->content_pending ?: $userArticle->content_public;
@endphp
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-serif font-semibold text-primary-800 mb-6">编辑投稿</h1>

    <x-forbidden-content-alert />
    @include('partials.forbidden-content-live-scan-panel')

    @if($userArticle->status === \App\Models\UserArticle::STATUS_PUBLISHED)
    <div class="p-4 rounded-xl bg-primary-50/80 border border-primary-200 text-sm text-dark-800/80 mb-6">
        已上线正文仍对读者展示；此处修改保存后需再次「提交审核」，通过后才会替换线上版本。
    </div>
    @endif

    <form id="user-article-form" method="POST" action="{{ route('front.my.articles.update', $userArticle) }}" class="space-y-5 bg-white rounded-2xl border border-haze-200 p-6 shadow-sm">
        @csrf
        @method('PUT')
        @if($userArticle->status !== \App\Models\UserArticle::STATUS_PUBLISHED)
        <div>
            <label class="block text-sm font-medium text-dark-800/80 mb-1">分类 <span class="text-red-500">*</span></label>
            @if($categories->isEmpty())
            <div class="w-full rounded-xl border border-haze-200 bg-haze-50 px-3 py-2.5 text-sm text-dark-800/80">
                投稿可选分类列表为空（可能管理员已关闭「允许用户投稿」）。您仍可保存本条稿件；分类将保持为当前分类。
            </div>
            <input type="hidden" name="category_id" value="{{ old('category_id', $userArticle->category_id) }}">
            @else
            <select name="category_id" required class="w-full rounded-xl border-haze-200">
                @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(old('category_id', $userArticle->category_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @endif
            @error('category_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        @endif
        <div>
            <label class="block text-sm font-medium text-dark-800/80 mb-1">标题 <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $userArticle->title) }}" required maxlength="120" class="w-full rounded-xl border-haze-200">
            <div id="forbidden-title-preview" class="hidden mt-1 text-sm leading-relaxed"></div>
            @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-dark-800/80 mb-1">
                正文 <span class="text-red-500">*</span>
                <span id="forbidden-body-badge" class="hidden ml-2 text-xs font-normal text-red-600"></span>
            </label>
            <p class="text-xs text-dark-800/50 mb-1">与后台文章相同的可视化编辑器；字数按纯文本计算。</p>
            {{-- 勿加 required：TinyMCE 会隐藏 textarea，浏览器会认为正文为空而静默拦截提交 --}}
            <textarea id="user-article-content" name="content" rows="14" class="w-full rounded-xl border-haze-200 font-sans text-sm">{{ old('content', $defaultBody) }}</textarea>
            <p id="user-article-content-client-error" class="text-red-600 text-sm mt-1 hidden" role="alert"></p>
            @error('content')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-dark-800/80 mb-1">标签（最多 3 个，逗号分隔）</label>
            <input type="text" name="tags_csv" value="{{ old('tags_csv', $userArticle->tags->pluck('name')->implode('，')) }}" class="w-full rounded-xl border-haze-200">
            <div id="forbidden-tags-preview" class="hidden mt-1 text-sm leading-relaxed"></div>
            @error('tags')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" name="action" value="save" class="px-5 py-2.5 rounded-xl bg-haze-200 text-dark-800 hover:bg-haze-300 text-sm">保存</button>
            @if($userArticle->status !== \App\Models\UserArticle::STATUS_PENDING_REVIEW)
            <button type="submit" name="action" value="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 text-white hover:bg-primary-700 text-sm">保存并提交审核</button>
            @endif
            <a href="{{ route('front.my.profile', ['page' => 'articles']) }}" class="px-5 py-2.5 text-sm text-primary-600 hover:underline">返回列表</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('front.my.articles.partials.tinymce', ['formId' => 'user-article-form', 'textareaId' => 'user-article-content'])
@include('partials.forbidden-content-live-scan-init', [
    'scanUrl' => route('front.forbidden-content.scan'),
    'context' => 'user_article',
    'titleSelector' => 'input[name="title"]',
    'bodySelector' => '#user-article-content',
    'bodyTinymce' => true,
    'tagsSelector' => 'input[name="tags_csv"]',
])
@endpush
