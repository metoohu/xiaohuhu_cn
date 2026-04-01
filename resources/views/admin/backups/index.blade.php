@extends('admin.layouts.master')

@section('title', '备份管理 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">备份管理</h2>
        <form action="{{ route('admin.backups.store') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">创建备份</button>
        </form>
    </div>

    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2">文件名</th>
                <th class="text-left py-2">大小</th>
                <th class="text-left py-2">时间</th>
                <th class="text-left py-2">操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($files as $f)
            <tr class="border-b border-slate-100">
                <td class="py-2">{{ $f['name'] }}</td>
                <td class="py-2">{{ number_format($f['size'] / 1024, 1) }} KB</td>
                <td class="py-2">{{ date('Y-m-d H:i:s', $f['time']) }}</td>
                <td class="py-2">
                    <div class="admin-table-actions">
                        <a href="{{ route('admin.backups.download', $f['name']) }}" class="admin-btn-action admin-btn-action--primary">下载</a>
                        <form action="{{ route('admin.backups.destroy', $f['name']) }}" method="POST" onsubmit="return confirm('确定删除此备份？')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn-action admin-btn-action--danger">删除</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-4 text-center text-slate-500">暂无备份</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
