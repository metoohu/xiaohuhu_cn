@extends('admin.layouts.master')

@section('title', '个人中心 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-2xl">
    <h2 class="text-xl font-bold mb-4">个人中心</h2>

    <div class="border-b pb-4 mb-4">
        <h3 class="font-semibold mb-2">基本资料</h3>
        <form method="POST" action="{{ route('admin.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">用户名</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">邮箱</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded border-slate-300">
                </div>
            </div>
            <button type="submit" class="mt-4 px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700">更新资料</button>
        </form>
    </div>

    <div class="border-b pb-4 mb-4">
        <h3 class="font-semibold mb-2">修改密码</h3>
        <form method="POST" action="{{ route('admin.profile.password') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">当前密码</label>
                    <input type="password" name="current_password" required class="w-full rounded border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">新密码</label>
                    <input type="password" name="password" required class="w-full rounded border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">确认新密码</label>
                    <input type="password" name="password_confirmation" required class="w-full rounded border-slate-300">
                </div>
            </div>
            <button type="submit" class="mt-4 px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700">修改密码</button>
        </form>
    </div>

    <div>
        <a href="{{ route('admin.profile.logs') }}" class="text-blue-600 hover:underline">查看登录日志</a>
    </div>
</div>
@endsection
