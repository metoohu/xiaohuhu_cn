@extends('admin.layouts.master')

@section('title', '前台会员 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">前台注册会员</h2>
    <p class="text-sm text-slate-500 mb-4">管理网站注册用户，可禁言（禁言后不可发表评论）。「用户管理」菜单仍为后台管理员账号。</p>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="昵称或邮箱" class="rounded border-slate-300">
        <select name="banned" class="rounded border-slate-300">
            <option value="">全部</option>
            <option value="1" {{ request('banned') === '1' ? 'selected' : '' }}>已禁言</option>
            <option value="0" {{ request('banned') === '0' ? 'selected' : '' }}>正常</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">搜索</button>
    </form>

    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">昵称</th>
                <th class="text-left py-2">邮箱</th>
                <th class="text-left py-2">评论数</th>
                <th class="text-left py-2">表情数</th>
                <th class="text-left py-2">状态</th>
                <th class="text-left py-2">注册时间</th>
                <th class="text-left py-2">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($members as $m)
            <tr class="border-b border-slate-100">
                <td class="py-2">{{ $m->id }}</td>
                <td class="py-2">{{ $m->name }}</td>
                <td class="py-2">{{ $m->email }}</td>
                <td class="py-2">{{ $m->comments_count }}</td>
                <td class="py-2">{{ $m->stickers_count }}</td>
                <td class="py-2">
                    @if($m->isCommentBanned())
                    <span class="text-red-600">禁言</span>
                    @else
                    <span class="text-green-600">正常</span>
                    @endif
                </td>
                <td class="py-2">{{ $m->created_at->format('Y-m-d H:i') }}</td>
                <td class="py-2">
                    <a href="{{ route('admin.members.show', $m) }}" class="text-blue-600 hover:underline">查看</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $members->links() }}</div>
</div>
@endsection
