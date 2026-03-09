@extends('admin.layouts.master')

@section('title', '仪表盘 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">仪表盘</h2>

    {{-- 統計卡片 --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-slate-500 text-sm">后台用户</div>
            <div class="text-2xl font-bold mt-1">{{ $stats['users_count'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-slate-500 text-sm">文章数</div>
            <div class="text-2xl font-bold mt-1">{{ $stats['articles_count'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-slate-500 text-sm">分类数</div>
            <div class="text-2xl font-bold mt-1">{{ $stats['categories_count'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-slate-500 text-sm">评论数</div>
            <div class="text-2xl font-bold mt-1">{{ $stats['comments_count'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-slate-500 text-sm">公司信息</div>
            <div class="text-2xl font-bold mt-1">{{ $stats['companies_count'] ?? 0 }}</div>
        </div>
    </div>

    {{-- 快捷操作 --}}
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold mb-3">快捷操作</h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">新增用户</a>
            <a href="{{ route('admin.articles.create') }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">新增文章</a>
            <a href="{{ route('admin.categories.create') }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">新增分类</a>
            @if ($pendingComments > 0)
                <a href="{{ route('admin.comments.index') }}?status=pending" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-500 text-sm">待审评论 ({{ $pendingComments }})</a>
            @endif
        </div>
    </div>

    {{-- 最近文章 --}}
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold mb-3">最近文章</h3>
        @if ($recentArticles->isEmpty())
            <p class="text-slate-500 text-sm">暂无文章</p>
        @else
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">标题</th>
                        <th class="text-left py-2">分类</th>
                        <th class="text-left py-2">状态</th>
                        <th class="text-left py-2">时间</th>
                        <th class="text-left py-2">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentArticles as $a)
                    <tr class="border-b border-slate-100">
                        <td class="py-2">{{ Str::limit($a->title, 30) }}</td>
                        <td class="py-2">{{ $a->category?->name ?? '-' }}</td>
                        <td class="py-2">
                            @if ($a->status === 'published') <span class="text-green-600">已发布</span>
                            @elseif ($a->status === 'draft') <span class="text-slate-500">草稿</span>
                            @else <span class="text-amber-600">审核中</span>
                            @endif
                        </td>
                        <td class="py-2">{{ $a->created_at->format('Y-m-d H:i') }}</td>
                        <td class="py-2"><a href="{{ route('admin.articles.edit', $a) }}" class="text-blue-600 hover:underline">编辑</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
