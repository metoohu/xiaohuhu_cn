@extends('admin.layouts.master')

@section('title', '登录日志 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">登录日志</h2>
        <a href="{{ route('admin.profile.edit') }}" class="text-blue-600 hover:underline">返回个人中心</a>
    </div>

    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2">状态</th>
                <th class="text-left py-2">IP</th>
                <th class="text-left py-2">设备</th>
                <th class="text-left py-2">时间</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
            <tr class="border-b border-slate-100">
                <td class="py-2">{{ $log->status ? '成功' : '失敗' }}</td>
                <td class="py-2">{{ $log->ip }}</td>
                <td class="py-2">{{ Str::limit($log->user_agent, 50) }}</td>
                <td class="py-2">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
