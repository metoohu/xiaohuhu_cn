@extends('admin.layouts.master')

@section('title', '操作日志 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">操作日志</h2>
        <a href="{{ route('admin.logs.index') }}" class="text-blue-600 hover:underline">返回</a>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="操作关键字" class="rounded border-slate-300">
        <input type="text" name="module" value="{{ request('module') }}" placeholder="模块" class="rounded border-slate-300">
        <button type="submit" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">搜索</button>
    </form>

    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">用户</th>
                <th class="text-left py-2">操作</th>
                <th class="text-left py-2">模块</th>
                <th class="text-left py-2">IP</th>
                <th class="text-left py-2">时间</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
            <tr class="border-b border-slate-100">
                <td class="py-2">{{ $log->id }}</td>
                <td class="py-2">{{ $log->adminUser?->name ?? '-' }}</td>
                <td class="py-2">{{ $log->action }}</td>
                <td class="py-2">{{ $log->module ?? '-' }}</td>
                <td class="py-2">{{ $log->ip }}</td>
                <td class="py-2">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
