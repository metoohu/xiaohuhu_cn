@extends('admin.layouts.master')

@section('title', '违规记录详情 #' . $violation->id . ' - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-4xl">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <h2 class="text-xl font-bold">违规记录 #{{ $violation->id }}</h2>
        <a href="{{ route('admin.forbidden-word-violations.index') }}" class="admin-btn-action admin-btn-action--neutral">返回列表</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-50 text-green-800 rounded text-sm">{{ session('success') }}</div>
    @endif

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm mb-6">
        <div>
            <dt class="text-slate-500">内容类型</dt>
            <dd>{{ $contentTypeLabels[$violation->content_type] ?? $violation->content_type }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">内容 ID</dt>
            <dd>{{ $violation->content_id ?? '—' }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-slate-500">标题快照</dt>
            <dd>{{ $violation->content_title_snapshot ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">命中字段</dt>
            <dd>{{ $violation->field }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">命中词</dt>
            <dd class="font-medium text-red-700">{{ $violation->matched_word }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">分类</dt>
            <dd>{{ $violation->category_slug }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">动作</dt>
            <dd>{{ $violation->action }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">状态</dt>
            <dd>{{ $statusLabels[$violation->status] ?? $violation->status }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">检测时间</dt>
            <dd>{{ $violation->checked_at?->format('Y-m-d H:i:s') }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">处理人</dt>
            <dd>{{ $violation->handler?->name ?? '—' }}</dd>
        </div>
    </dl>

    <div class="mb-4">
        <h3 class="font-semibold mb-2">原文摘录</h3>
        <pre class="p-3 bg-slate-50 rounded text-sm whitespace-pre-wrap border border-slate-200">{{ $violation->original_excerpt }}</pre>
    </div>

    @if ($violation->replaced_excerpt)
    <div class="mb-6">
        <h3 class="font-semibold mb-2">替换后摘录</h3>
        <pre class="p-3 bg-green-50 rounded text-sm whitespace-pre-wrap border border-green-200">{{ $violation->replaced_excerpt }}</pre>
    </div>
    @endif

    @if ($violation->status !== 'resolved')
    <form action="{{ route('admin.forbidden-word-violations.resolve', $violation) }}" method="POST">
        @csrf
        @method('PATCH')
        <button type="submit" class="admin-toolbar-btn admin-toolbar-btn--green">标记为已处理</button>
    </form>
    @endif
</div>
@endsection
