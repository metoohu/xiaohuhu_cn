@extends('admin.layouts.master')

@section('title', '编辑角色 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-md">
    <h2 class="text-xl font-bold mb-4">编辑角色</h2>
    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">名称</label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">描述</label>
                <input type="text" name="description" value="{{ old('description', $role->description) }}" class="w-full rounded border-slate-300">
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700">更新</button>
            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">取消</a>
        </div>
    </form>
</div>
@endsection
