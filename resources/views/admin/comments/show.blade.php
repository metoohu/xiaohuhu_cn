@extends('admin.layouts.master')

@section('title', '评论详情 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">评论详情</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <dt class="text-slate-500">ID</dt><dd>{{ $comment->id }}</dd>
        <dt class="text-slate-500">文章</dt><dd><a href="{{ route('admin.articles.edit', $comment->article) }}" class="text-blue-600">{{ $comment->article?->title }}</a></dd>
        <dt class="text-slate-500">作者</dt><dd>{{ $comment->user?->name ?? $comment->author_name ?? '访客' }}</dd>
        <dt class="text-slate-500">状态</dt><dd>{{ $comment->status }}</dd>
        <dt class="text-slate-500">時間</dt><dd>{{ $comment->created_at->format('Y-m-d H:i') }}</dd>
    </dl>
    <div class="mt-4">
        <label class="text-sm font-medium text-slate-500">内容</label>
        <div class="mt-1 p-3 bg-slate-50 rounded break-words">{!! $comment->content_html !!}</div>
    </div>
    <div class="mt-4">
        <a href="{{ route('admin.comments.edit', $comment) }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">编辑</a>
        <a href="{{ route('admin.comments.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300 text-sm ml-2">返回</a>
    </div>
</div>
@endsection
