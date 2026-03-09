@extends('admin.layouts.master')

@section('title', '角色详情 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">角色详情</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <dt class="text-slate-500">ID</dt><dd>{{ $role->id }}</dd>
        <dt class="text-slate-500">名称</dt><dd>{{ $role->name }}</dd>
        <dt class="text-slate-500">描述</dt><dd>{{ $role->description ?? '-' }}</dd>
    </dl>
    <h3 class="mt-4 font-semibold">关联用户</h3>
    <ul class="mt-2 list-disc list-inside">
        @foreach ($role->users as $u)
            <li>{{ $u->name }} ({{ $u->email }})</li>
        @endforeach
    </ul>
    <div class="mt-4">
        <a href="{{ route('admin.roles.edit', $role) }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">编辑</a>
        <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300 text-sm ml-2">返回</a>
    </div>
</div>
@endsection
