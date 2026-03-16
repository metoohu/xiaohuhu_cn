@extends('admin.layouts.master')

@section('title', '分类管理 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">分类管理</h2>
        <a href="{{ route('admin.categories.create') }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">新增分类</a>
    </div>

    <form action="{{ route('admin.categories.batch') }}" method="POST" id="categoryBatchForm" class="mb-4 flex flex-wrap items-center gap-2">
        @csrf
        <button type="submit" name="action" value="enable" class="px-3 py-1.5 bg-green-100 text-green-800 rounded text-sm hover:bg-green-200">批量启用</button>
        <button type="submit" name="action" value="disable" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-sm hover:bg-slate-200">批量禁用</button>
        <button type="submit" name="action" value="modify" class="px-3 py-1.5 bg-blue-100 text-blue-800 rounded text-sm hover:bg-blue-200">批量修改</button>
        <select name="parent_id" class="rounded border-slate-300 text-sm py-1 px-2">
            <option value="">父级不变</option>
            <option value="0">无（顶级）</option>
            @foreach($parentOptions ?? [] as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>
        <input type="number" name="sort" placeholder="排序" min="0" class="rounded border-slate-300 text-sm py-1 px-2 w-20">
        <button type="submit" name="action" value="delete" class="px-3 py-1.5 bg-red-100 text-red-800 rounded text-sm hover:bg-red-200">批量删除</button>
    </form>

    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2 w-10"><input type="checkbox" id="categorySelectAll"></th>
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">名称</th>
                <th class="text-left py-2">slug</th>
                <th class="text-left py-2">父级</th>
                <th class="text-left py-2">排序</th>
                <th class="text-left py-2">状态</th>
                <th class="text-left py-2">文章数</th>
                <th class="text-left py-2">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $c)
            <tr class="border-b border-slate-100">
                <td class="py-2"><input type="checkbox" form="categoryBatchForm" name="ids[]" value="{{ $c->id }}" class="category-checkbox"></td>
                <td class="py-2">{{ $c->id }}</td>
                <td class="py-2">{{ $c->name }}</td>
                <td class="py-2 text-slate-500">{{ $c->slug ?? '-' }}</td>
                <td class="py-2">{{ $c->parent?->name ?? '-' }}</td>
                <td class="py-2">{{ $c->sort }}</td>
                <td class="py-2">
                    @if(($c->status ?? 1) == 1)
                        <span class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-800">启用</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600">禁用</span>
                    @endif
                </td>
                <td class="py-2">{{ $c->articles_count ?? 0 }}</td>
                <td class="py-2">
                    <a href="{{ route('admin.categories.edit', ['category' => $c->id]) }}" class="text-blue-600 hover:underline">编辑</a>
                    <form action="{{ route('admin.categories.destroy', ['category' => $c->id]) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">删除</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
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
