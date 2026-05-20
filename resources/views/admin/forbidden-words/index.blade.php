@extends('admin.layouts.master')

@section('title', '违禁词库 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
        <h2 class="text-xl font-bold text-slate-900">违禁词库</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.forbidden-words.create') }}" class="admin-toolbar-btn admin-toolbar-btn--dark shrink-0">新增词条</a>
            <a href="{{ route('admin.forbidden-words.import') }}" class="admin-toolbar-btn admin-toolbar-btn--blue shrink-0">Excel 导入</a>
            <a href="{{ route('admin.forbidden-words.export') }}" class="admin-toolbar-btn admin-toolbar-btn--slate shrink-0">Excel 导出</a>
        </div>
    </div>

    @php
        $filterBase = request()->except(['is_enabled', 'page']);
        $filterAll = route('admin.forbidden-words.index', $filterBase);
        $filterEnabled = route('admin.forbidden-words.index', array_merge($filterBase, ['is_enabled' => 1]));
        $filterDisabled = route('admin.forbidden-words.index', array_merge($filterBase, ['is_enabled' => 0]));
        $currentEnabled = request('is_enabled');
    @endphp

    <form method="GET" action="{{ route('admin.forbidden-words.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-slate-500 mb-1">分类</label>
            <select name="category_id" class="rounded border-slate-300 text-sm py-1 px-2">
                <option value="">全部</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">关键词</label>
            <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="词条包含…" class="rounded border-slate-300 text-sm py-1 px-2 w-40">
        </div>
        <button type="submit" class="admin-toolbar-btn admin-toolbar-btn--dark">筛选</button>
        @if(request()->hasAny(['category_id', 'keyword', 'is_enabled']))
            <a href="{{ route('admin.forbidden-words.index') }}" class="text-xs text-slate-500 hover:text-slate-700 underline-offset-2 hover:underline">清除筛选</a>
        @endif
    </form>

    <div class="mb-4 flex flex-wrap items-center gap-4">
        <div class="admin-filter-tabs" role="tablist" aria-label="启用状态">
            <a href="{{ $filterAll }}" class="@if($currentEnabled === null || $currentEnabled === '') is-active @endif">全部</a>
            <a href="{{ $filterEnabled }}" class="@if($currentEnabled === '1') is-active is-active-green @endif">启用</a>
            <a href="{{ $filterDisabled }}" class="@if($currentEnabled === '0') is-active is-active-slate @endif">禁用</a>
        </div>
        <form action="{{ route('admin.forbidden-words.batch') }}" method="POST" id="forbiddenWordBatchForm" class="flex flex-wrap items-center gap-2">
            @csrf
            <button type="submit" name="action" value="enable" class="admin-toolbar-btn admin-toolbar-btn--green">批量启用</button>
            <button type="submit" name="action" value="disable" class="admin-toolbar-btn admin-toolbar-btn--slate">批量禁用</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-600">
                    <th class="text-left py-3 px-3 font-semibold w-10"><input type="checkbox" id="forbiddenWordSelectAll"></th>
                    <th class="text-left py-3 px-3 font-semibold">ID</th>
                    <th class="text-left py-3 px-3 font-semibold">分类</th>
                    <th class="text-left py-3 px-3 font-semibold">词条</th>
                    <th class="text-left py-3 px-3 font-semibold">匹配</th>
                    <th class="text-left py-3 px-3 font-semibold">替换词</th>
                    <th class="text-left py-3 px-3 font-semibold">状态</th>
                    <th class="text-left py-3 px-3 font-semibold">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($words as $w)
                <tr class="hover:bg-slate-50/80">
                    <td class="py-3 px-3"><input type="checkbox" form="forbiddenWordBatchForm" name="ids[]" value="{{ $w->id }}" class="forbidden-word-checkbox"></td>
                    <td class="py-3 px-3 tabular-nums">{{ $w->id }}</td>
                    <td class="py-3 px-3">{{ $w->category?->name ?? '-' }}</td>
                    <td class="py-3 px-3 font-medium text-slate-800">{{ $w->word }}</td>
                    <td class="py-3 px-3 text-slate-500">{{ $w->match_type === 'fuzzy' ? '模糊' : '精确' }}</td>
                    <td class="py-3 px-3 text-slate-600">{{ $w->replacement ?? '—' }}</td>
                    <td class="py-3 px-3">
                        @if($w->is_enabled)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">启用</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">禁用</span>
                        @endif
                    </td>
                    <td class="py-3 px-3">
                        <div class="admin-table-actions">
                            <a href="{{ route('admin.forbidden-words.edit', $w) }}" class="admin-btn-action admin-btn-action--primary">编辑</a>
                            <form action="{{ route('admin.forbidden-words.destroy', $w) }}" method="POST" class="inline" onsubmit="return confirm('确定删除该词条？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn-action admin-btn-action--danger">删除</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-8 text-center text-slate-500">暂无词条</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $words->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('forbiddenWordSelectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.forbidden-word-checkbox').forEach(function (cb) {
        cb.checked = this.checked;
    }, this);
});
</script>
@endpush
