@extends('admin.layouts.master')

@section('title', '菜单管理 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl font-bold">左侧菜单管理</h2>
            <p class="text-sm text-slate-500 mt-1">可增删菜单、设置下级、绑定 Laravel 路由名或自定义 URL，并用上移/下移在同级别内排序。</p>
        </div>
        <a href="{{ route('admin.menu-items.create') }}" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 text-sm">新增顶级菜单</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left">
                    <th class="py-2 pr-2">标题</th>
                    <th class="py-2 pr-2">路由名</th>
                    <th class="py-2 pr-2">URL</th>
                    <th class="py-2 pr-2">高亮规则</th>
                    <th class="py-2 pr-2 w-16">排序</th>
                    <th class="py-2 pr-2">状态</th>
                    <th class="py-2 min-w-[14rem]">操作</th>
                </tr>
            </thead>
            <tbody>
                @include('admin.menu-items._tree-rows', ['items' => $tree, 'depth' => 0])
            </tbody>
        </table>
    </div>

    <div class="mt-6 p-4 bg-slate-50 rounded-lg text-sm text-slate-600 space-y-2">
        <p><strong>说明：</strong>「路由名」请选已注册的 <code class="bg-white px-1 rounded border">admin.*</code> 名称；若填「URL」，需以 <code class="bg-white px-1 rounded border">/</code> 开头或完整 http 地址。</p>
        <p>「高亮规则」可选，如 <code class="bg-white px-1 rounded border">admin.users.*</code>，用于子页面时仍高亮父级菜单。</p>
    </div>
</div>
@endsection
