@extends('admin.layouts.master')

@section('title', '用户管理 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
        <h2 class="text-xl font-bold">用户管理</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.export') }}" class="px-4 py-2 bg-slate-600 text-white rounded hover:bg-slate-500 text-sm">导出</a>
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">新增用户</a>
        </div>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="搜索用户名/邮箱" class="rounded border-slate-300">
        <select name="status" class="rounded border-slate-300">
            <option value="">全部状态</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>启用</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>禁用</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">搜索</button>
    </form>

    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2"><input type="checkbox" id="selectAll"></th>
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">用户名</th>
                <th class="text-left py-2">邮箱</th>
                <th class="text-left py-2">角色</th>
                <th class="text-left py-2">状态</th>
                <th class="text-left py-2">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $u)
            <tr class="border-b border-slate-100">
                <td class="py-2"><input type="checkbox" form="batchForm" name="ids[]" value="{{ $u->id }}" class="user-checkbox" {{ $u->isSuperAdmin() ? 'disabled' : '' }}></td>
                <td class="py-2">{{ $u->id }}</td>
                <td class="py-2">{{ $u->name }}</td>
                <td class="py-2">{{ $u->email }}</td>
                <td class="py-2">{{ $u->roles->pluck('name')->join(', ') ?: '-' }}</td>
                <td class="py-2">{{ $u->status ? '启用' : '禁用' }}</td>
                <td class="py-2">
                    <a href="{{ route('admin.users.edit', $u) }}" class="text-blue-600 hover:underline">编辑</a>
                    @if (!$u->isSuperAdmin())
                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">删除</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <form action="{{ route('admin.users.batch') }}" method="POST" id="batchForm">
        @csrf
        <div class="mt-4 flex justify-between items-center">
            <div class="flex gap-2">
                <button type="submit" name="action" value="enable" class="px-3 py-1 bg-green-100 text-green-800 rounded text-sm">批量启用</button>
                <button type="submit" name="action" value="disable" class="px-3 py-1 bg-amber-100 text-amber-800 rounded text-sm">批量禁用</button>
                <button type="submit" name="action" value="delete" class="px-3 py-1 bg-red-100 text-red-800 rounded text-sm">批量删除</button>
            </div>
            <div>{{ $users->links() }}</div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.user-checkbox:not([disabled])').forEach(cb => cb.checked = this.checked);
});
document.getElementById('batchForm')?.addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    if (checked.length === 0) { e.preventDefault(); alert('请选择用户'); }
});
</script>
@endpush
@endsection
