@extends('admin.layouts.master')

@section('title', '文章详情 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">{{ $article->title }}</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
        <dt class="text-slate-500">分类</dt><dd>{{ $article->category?->name ?? '-' }}</dd>
        <dt class="text-slate-500">状态</dt><dd>
            @if ($article->status === 'published') <span class="text-green-600">已发布</span>
            @elseif ($article->status === 'draft') <span class="text-slate-500">草稿</span>
            @else <span class="text-amber-600">待审核</span>
            @endif
        </dd>
        <dt class="text-slate-500">作者</dt><dd>{{ $article->adminUser?->name ?? '-' }}</dd>
        <dt class="text-slate-500">创建时间</dt><dd>{{ $article->created_at->format('Y-m-d H:i') }}</dd>
        @if ($article->reviewed_at)
        <dt class="text-slate-500">审核时间</dt><dd>{{ $article->reviewed_at->format('Y-m-d H:i') }}</dd>
        @if ($article->review_comment)
        <dt class="text-slate-500">审核意见</dt><dd class="text-amber-700">{{ $article->review_comment }}</dd>
        @endif
        @endif
    </dl>
    <div class="border-t pt-4 prose max-w-none">
        {!! $article->content !!}
    </div>

    @if ($article->status === 'review')
    <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
        <h3 class="font-bold text-amber-800 mb-3">人工审核</h3>
        <p class="text-sm text-slate-600 mb-4">请阅读全文后，选择通过或驳回。驳回时可填写审核意见，作者将看到该意见以便修改。</p>
        <div class="flex flex-wrap gap-4 items-start">
            <form action="{{ route('admin.articles.approve', $article) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">审核通过</button>
            </form>
            <form action="{{ route('admin.articles.reject', $article) }}" method="POST" class="inline flex-1 min-w-[280px]">
                @csrf
                <div class="flex gap-2 flex-wrap">
                    <input type="text" name="review_comment" placeholder="驳回意见（选填）" class="flex-1 min-w-[200px] rounded border-slate-300 px-3 py-2 text-sm">
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm">驳回</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('admin.articles.edit', $article) }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">编辑</a>
        <a href="{{ route('admin.articles.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300 text-sm ml-2">返回</a>
    </div>
</div>
@endsection
