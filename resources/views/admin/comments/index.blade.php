@extends('admin.layouts.master')

@section('title', '评论管理 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">评论管理</h2>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <select name="status" class="rounded border-slate-300">
            <option value="">全部状态</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>待审核</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>已通过</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>已拒绝</option>
        </select>
        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="内容搜索" class="rounded border-slate-300">
        <button type="submit" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">搜索</button>
    </form>

    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">文章</th>
                <th class="text-left py-2">内容</th>
                <th class="text-left py-2">作者</th>
                <th class="text-left py-2">状态</th>
                <th class="text-left py-2">時間</th>
                <th class="text-left py-2">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($comments as $c)
            <tr class="border-b border-slate-100">
                <td class="py-2">{{ $c->id }}</td>
                <td class="py-2">{{ Str::limit($c->article?->title ?? '-', 20) }}</td>
                <td class="py-2">{{ Str::limit($c->content, 50) }}</td>
                <td class="py-2">{{ $c->user?->name ?? $c->author_name ?? '访客' }}</td>
                <td class="py-2">
                    @if ($c->status === 'pending') <span class="text-amber-600">待审核</span>
                    @elseif ($c->status === 'approved') <span class="text-green-600">已通过</span>
                    @else <span class="text-red-600">已拒绝</span>
                    @endif
                </td>
                <td class="py-2">{{ $c->created_at->format('Y-m-d H:i') }}</td>
                <td class="py-2">
                    <a href="{{ route('admin.comments.edit', $c) }}" class="text-blue-600 hover:underline">编辑</a>
                    @if ($c->status === 'pending')
                        <form action="{{ route('admin.comments.approve', $c) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-green-600 hover:underline">通过</button>
                        </form>
                        <form action="{{ route('admin.comments.reject', $c) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:underline">拒绝</button>
                        </form>
                    @endif
                    <form action="{{ route('admin.comments.destroy', $c) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">删除</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $comments->links() }}</div>
</div>
@endsection
