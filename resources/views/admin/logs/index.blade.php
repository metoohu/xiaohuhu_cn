@extends('admin.layouts.master')

@section('title', '日志管理 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">日志管理</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('admin.logs.operation') }}" class="block p-4 border rounded hover:bg-slate-50">
            <h3 class="font-semibold">操作日志</h3>
            <p class="text-sm text-slate-500 mt-1">查看后台操作记录</p>
        </a>
        <a href="{{ route('admin.logs.error') }}" class="block p-4 border rounded hover:bg-slate-50">
            <h3 class="font-semibold">错误日志</h3>
            <p class="text-sm text-slate-500 mt-1">查看系统错误日志</p>
        </a>
    </div>
</div>
@endsection
