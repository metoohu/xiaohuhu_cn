@extends('admin.layouts.master')

@section('title', '新增文章 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">新增文章</h2>
    <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data" id="article-create-form">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">标题</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">分类</label>
                <select name="category_id" class="w-full rounded border-slate-300">
                    <option value="">无</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">封面图</label>
                <input type="file" name="cover_image" accept="image/*" class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">状态</label>
                <select name="status" class="w-full rounded border-slate-300">
                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>草稿</option>
                    <option value="review" {{ old('status') === 'review' ? 'selected' : '' }}>提交审核</option>
                </select>
                <p class="text-xs text-slate-500 mt-1">选择「提交审核」后，需管理员审核通过才会在前台发布</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">内容</label>
                <textarea name="content" id="article-content" rows="15" class="w-full rounded border-slate-300">{{ old('content') }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" id="article-submit-btn" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed">创建</button>
            <a href="{{ route('admin.articles.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">取消</a>
        </div>
    </form>
</div>
@push('scripts')
@include('admin.articles.partials.tinymce', ['formId' => 'article-create-form'])
@endpush
@endsection
