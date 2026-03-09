@extends('admin.layouts.master')

@section('title', '新增用户 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-md">
    <h2 class="text-xl font-bold mb-4">新增用户</h2>
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">用户名</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">邮箱</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">密码</label>
                <input type="password" name="password" required class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">确认密码</label>
                <input type="password" name="password_confirmation" required class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">状态</label>
                <select name="status" class="w-full rounded border-slate-300">
                    <option value="1">启用</option>
                    <option value="0">禁用</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">角色</label>
                @foreach ($roles as $r)
                    <label class="block"><input type="checkbox" name="roles[]" value="{{ $r->id }}"> {{ $r->name }}</label>
                @endforeach
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700">创建</button>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">取消</a>
        </div>
    </form>
</div>
@endsection
