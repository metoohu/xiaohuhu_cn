@extends('admin.layouts.master')

@section('title', '用户详情 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">用户详情</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <dt class="text-slate-500">ID</dt><dd>{{ $user->id }}</dd>
        <dt class="text-slate-500">用户名</dt><dd>{{ $user->name }}</dd>
        <dt class="text-slate-500">邮箱</dt><dd>{{ $user->email }}</dd>
        <dt class="text-slate-500">状态</dt><dd>{{ $user->status ? '启用' : '禁用' }}</dd>
        <dt class="text-slate-500">角色</dt><dd>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</dd>
        <dt class="text-slate-500">创建时间</dt><dd>{{ $user->created_at->format('Y-m-d H:i') }}</dd>
    </dl>
    <div class="mt-4">
        <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">编辑</a>
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300 text-sm ml-2">返回</a>
    </div>
</div>
@endsection
