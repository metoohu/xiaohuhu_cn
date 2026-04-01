@extends('admin.layouts.master')

@section('title', '分类管理 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
        <h2 class="text-xl font-bold text-slate-900">分类管理</h2>
        <a href="{{ route('admin.categories.create') }}" class="admin-toolbar-btn admin-toolbar-btn--dark shrink-0">新增分类</a>
    </div>

    @php
        $filterBase = request()->except(['status', 'page']);
        $filterAll = route('admin.categories.index', $filterBase);
        $filterEnabled = route('admin.categories.index', array_merge($filterBase, ['status' => 1]));
        $filterDisabled = route('admin.categories.index', array_merge($filterBase, ['status' => 0]));
        $currentFilter = request('status');
    @endphp
    <div class="mb-4 flex flex-wrap items-center gap-4">
        <div class="admin-filter-tabs" role="tablist" aria-label="状态筛选">
            <a href="{{ $filterAll }}" class="@if($currentFilter === null || $currentFilter === '') is-active @endif">全部</a>
            <a href="{{ $filterEnabled }}" class="@if($currentFilter === '1') is-active is-active-green @endif">启用</a>
            <a href="{{ $filterDisabled }}" class="@if($currentFilter === '0') is-active is-active-slate @endif">禁用</a>
        </div>
        @if($currentFilter !== null && $currentFilter !== '')
        <a href="{{ $filterAll }}" class="text-xs text-slate-500 hover:text-slate-700 underline-offset-2 hover:underline">清除筛选</a>
        @endif
        <form action="{{ route('admin.categories.batch') }}" method="POST" id="categoryBatchForm" class="flex flex-wrap items-center gap-2">
        @csrf
        <button type="submit" name="action" value="enable" class="admin-toolbar-btn admin-toolbar-btn--green">批量启用</button>
        <button type="submit" name="action" value="disable" class="admin-toolbar-btn admin-toolbar-btn--slate">批量禁用</button>
        <button type="submit" name="action" value="modify" class="admin-toolbar-btn admin-toolbar-btn--blue">批量修改</button>
        <select name="parent_id" class="rounded border-slate-300 text-sm py-1 px-2">
            <option value="">父级不变</option>
            <option value="0">无（顶级）</option>
            @foreach($parentOptions ?? [] as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>
        <input type="number" name="sort" placeholder="排序" min="0" class="rounded border-slate-300 text-sm py-1 px-2 w-20">
        <button type="submit" name="action" value="delete" class="admin-toolbar-btn admin-toolbar-btn--red">批量删除</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600">
                <th class="text-left py-3 px-3 font-semibold w-10"><input type="checkbox" id="categorySelectAll"></th>
                <th class="text-left py-3 px-3 font-semibold">ID</th>
                <th class="text-left py-3 px-3 font-semibold">名称</th>
                <th class="text-left py-3 px-3 font-semibold">slug</th>
                <th class="text-left py-3 px-3 font-semibold">父级</th>
                <th class="text-left py-3 px-3 font-semibold">排序</th>
                <th class="text-left py-3 px-3 font-semibold">状态</th>
                <th class="text-left py-3 px-3 font-semibold">文章数</th>
                <th class="text-left py-3 px-3 font-semibold">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($categories as $c)
            <tr class="hover:bg-slate-50/80">
                <td class="py-3 px-3"><input type="checkbox" form="categoryBatchForm" name="ids[]" value="{{ $c->id }}" class="category-checkbox"></td>
                <td class="py-3 px-3">{{ $c->id }}</td>
                <td class="py-3 px-3 font-medium text-slate-800">{{ $c->name }}</td>
                <td class="py-3 px-3 text-slate-500 text-xs">{{ $c->slug ?? '-' }}</td>
                <td class="py-3 px-3">{{ $c->parent?->name ?? '-' }}</td>
                <td class="py-3 px-3 tabular-nums">{{ $c->sort }}</td>
                <td class="py-3 px-3">
                    @if(($c->status ?? 1) == 1)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">启用</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">禁用</span>
                    @endif
                </td>
                <td class="py-3 px-3 tabular-nums">{{ $c->articles_count ?? 0 }}</td>
                <td class="py-3 px-3">
                    <div class="admin-table-actions">
                        <a href="{{ route('admin.categories.edit', ['category' => $c->id]) }}" class="admin-btn-action admin-btn-action--primary">编辑</a>
                        <form action="{{ route('admin.categories.destroy', ['category' => $c->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn-action admin-btn-action--danger">删除</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div class="mt-4">{{ $categories->links() }}</div>
</div>

@push('scripts')
<script>
document.getElementById('categorySelectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = this.checked);
});
document.getElementById('categoryBatchForm')?.addEventListener('submit', function(e) {
    var checked = document.querySelectorAll('.category-checkbox:checked');
    if (checked.length === 0) {
        e.preventDefault();
        alert('请先选择要操作的分类');
    }
});
</script>
@endpush
@endsection
