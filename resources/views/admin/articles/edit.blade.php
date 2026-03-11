@extends('admin.layouts.master')

@section('title', '编辑文章 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">编辑文章</h2>
    <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data" id="article-edit-form">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">标题</label>
                <input type="text" name="title" value="{{ old('title', $article->title) }}" required class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">分类</label>
                <select name="category_id" class="w-full rounded border-slate-300">
                    <option value="">无</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" {{ old('category_id', $article->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">封面图</label>
                @if ($article->cover_image)
                    <p class="text-sm text-slate-500 mb-1">当前：<a href="{{ \Illuminate\Support\Facades\Storage::url($article->cover_image) }}" target="_blank" class="text-blue-600">查看</a></p>
                @endif
                <input type="file" name="cover_image" accept="image/*" class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">阅读量</label>
                <input type="number" name="click_num" value="{{ old('click_num', $article->click_num ?? 0) }}" min="0" class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">状态</label>
                <select name="status" class="w-full rounded border-slate-300">
                    <option value="draft" {{ old('status', $article->status) === 'draft' ? 'selected' : '' }}>草稿</option>
                    <option value="review" {{ old('status', $article->status) === 'review' ? 'selected' : '' }}>待审核</option>
                    <option value="published" {{ old('status', $article->status) === 'published' ? 'selected' : '' }}>已发布</option>
                </select>
                <p class="text-xs text-slate-500 mt-1">已发布文章可保持或改为草稿/待审核；新建文章需审核通过后才能发布</p>
            </div>
            @if ($article->review_comment)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                <label class="block text-sm font-medium text-amber-800 mb-1">审核意见（驳回原因）</label>
                <p class="text-sm text-amber-900">{{ $article->review_comment }}</p>
                @if ($article->reviewed_at)
                    <p class="text-xs text-amber-700 mt-1">审核时间：{{ $article->reviewed_at->format('Y-m-d H:i') }}</p>
                @endif
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium mb-1">内容</label>
                <textarea name="content" id="article-content" rows="15" class="w-full rounded border-slate-300">{{ old('content', $article->content) }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" id="article-submit-btn" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed">更新</button>
            <a href="{{ route('admin.articles.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">取消</a>
        </div>
    </form>
</div>
@push('scripts')
@include('admin.articles.partials.tinymce', ['formId' => 'article-edit-form'])
@endpush
@endsection
