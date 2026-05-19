@extends('admin.layouts.master')

@section('title', '用户投稿详情 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-4xl">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">用户投稿 #{{ $userArticle->id }}</h2>
        <a href="{{ route('admin.user-articles.index') }}" class="text-sm text-blue-600 hover:underline">返回列表</a>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm mb-6">
        <dt class="text-slate-500">标题</dt><dd class="font-medium">{{ $userArticle->title }}</dd>
        <dt class="text-slate-500">状态</dt><dd>{{ $userArticle->status }}</dd>
        <dt class="text-slate-500">会员</dt><dd>{{ $userArticle->user?->name }} ({{ $userArticle->user?->email }})</dd>
        <dt class="text-slate-500">分类</dt><dd>{{ $userArticle->category?->name }}</dd>
        <dt class="text-slate-500">标签</dt><dd>{{ $userArticle->tags->pluck('name')->join('、') ?: '-' }}</dd>
        <dt class="text-slate-500">提交时间</dt><dd>{{ $userArticle->submitted_at?->format('Y-m-d H:i') ?? '-' }}</dd>
        <dt class="text-slate-500">对外发布时间</dt><dd>{{ $userArticle->published_at?->format('Y-m-d H:i') ?? '-' }}</dd>
        @if($userArticle->rejection_reason)
        <dt class="text-slate-500">最近驳回原因</dt><dd class="text-red-700 sm:col-span-2">{{ $userArticle->rejection_reason }}</dd>
        @endif
    </dl>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-2">线上正文（content_public）</h3>
            <div class="p-3 bg-slate-50 rounded text-sm max-h-80 overflow-y-auto prose prose-sm max-w-none">{!! \App\Models\UserArticle::adminBodyPreviewHtml($userArticle->content_public) !!}</div>
        </div>
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-2">待审正文（content_pending）</h3>
            <div class="p-3 bg-amber-50/80 rounded text-sm max-h-80 overflow-y-auto prose prose-sm max-w-none">{!! \App\Models\UserArticle::adminBodyPreviewHtml($userArticle->content_pending) !!}</div>
        </div>
    </div>

    @if($userArticle->status === \App\Models\UserArticle::STATUS_PENDING_REVIEW)
    <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-4">
        <form action="{{ route('admin.user-articles.approve', $userArticle) }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">通过</button>
        </form>
        <form action="{{ route('admin.user-articles.reject', $userArticle) }}" method="POST" class="flex flex-wrap items-end gap-2">
            @csrf
            <div>
                <label class="block text-xs text-slate-500 mb-1">驳回原因</label>
                <input type="text" name="rejection_reason" required class="rounded border-slate-300 text-sm w-64">
            </div>
            <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm">驳回</button>
        </form>
    </div>
    @endif
</div>
@endsection
