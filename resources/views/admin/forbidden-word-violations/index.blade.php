@extends('admin.layouts.master')

@section('title', '违规记录 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <h2 class="text-xl font-bold">违禁词违规记录</h2>
        <a href="{{ route('admin.forbidden-word-violations.export', request()->query()) }}"
           class="admin-toolbar-btn admin-toolbar-btn--blue">导出 CSV</a>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
        <div>
            <label class="block text-xs text-slate-500 mb-1">关键词</label>
            <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="标题快照 / 命中词"
                   class="rounded border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">分类</label>
            <select name="category_slug" class="rounded border-slate-300 text-sm">
                <option value="">全部分类</option>
                <option value="compliance_redline" @selected(request('category_slug') === 'compliance_redline')>合规红线</option>
                <option value="tone_violation" @selected(request('category_slug') === 'tone_violation')>调性违规</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">状态</label>
            <select name="status" class="rounded border-slate-300 text-sm">
                <option value="">全部状态</option>
                @foreach ($statusLabels as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">检测自</label>
            <input type="date" name="checked_from" value="{{ request('checked_from') }}" class="rounded border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">检测至</label>
            <input type="date" name="checked_to" value="{{ request('checked_to') }}" class="rounded border-slate-300 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300 text-sm">筛选</button>
    </form>

    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">类型</th>
                <th class="text-left py-2">标题快照</th>
                <th class="text-left py-2">字段</th>
                <th class="text-left py-2">命中词</th>
                <th class="text-left py-2">动作</th>
                <th class="text-left py-2">状态</th>
                <th class="text-left py-2">检测时间</th>
                <th class="text-left py-2">操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($violations as $v)
            <tr class="border-b border-slate-100">
                <td class="py-2">{{ $v->id }}</td>
                <td class="py-2">{{ $contentTypeLabels[$v->content_type] ?? $v->content_type }}</td>
                <td class="py-2">{{ Str::limit($v->content_title_snapshot ?? '—', 28) }}</td>
                <td class="py-2">{{ $v->field }}</td>
                <td class="py-2">{{ $v->matched_word }}</td>
                <td class="py-2">{{ $v->action }}</td>
                <td class="py-2">{{ $statusLabels[$v->status] ?? $v->status }}</td>
                <td class="py-2">{{ $v->checked_at?->format('Y-m-d H:i') }}</td>
                <td class="py-2">
                    <div class="admin-table-actions">
                        <a href="{{ route('admin.forbidden-word-violations.show', $v) }}"
                           class="admin-btn-action admin-btn-action--primary">详情</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="py-6 text-center text-slate-500">暂无违规记录</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $violations->links() }}</div>
</div>
@endsection
