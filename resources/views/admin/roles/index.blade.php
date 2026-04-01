@extends('admin.layouts.master')

@section('title', '角色管理 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">角色管理</h2>
        <a href="{{ route('admin.roles.create') }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">新增角色</a>
    </div>
    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">名称</th>
                <th class="text-left py-2">描述</th>
                <th class="text-left py-2">用户数</th>
                <th class="text-left py-2">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roles as $r)
            <tr class="border-b border-slate-100">
                <td class="py-2">{{ $r->id }}</td>
                <td class="py-2">{{ $r->name }}</td>
                <td class="py-2">{{ $r->description ?? '-' }}</td>
                <td class="py-2">{{ $r->users_count ?? 0 }}</td>
                <td class="py-2">
                    <div class="admin-table-actions">
                        <a href="{{ route('admin.roles.edit', $r) }}" class="admin-btn-action admin-btn-action--primary">编辑</a>
                        @if ($r->name !== 'super_admin')
                            <form action="{{ route('admin.roles.destroy', $r) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn-action admin-btn-action--danger">删除</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $roles->links() }}</div>
</div>
@endsection
