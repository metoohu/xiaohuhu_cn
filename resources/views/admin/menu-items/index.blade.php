@extends('admin.layouts.master')

@section('title', '菜单管理 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-xl font-bold text-slate-900">左侧菜单管理</h2>
            <p class="text-sm text-slate-500 mt-1 max-w-2xl">可增删菜单、设置下级、绑定路由名或自定义 URL；同级内用「上移 / 下移」调整顺序。</p>
        </div>
        <a href="{{ route('admin.menu-items.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-800 text-white rounded-lg hover:bg-slate-700 text-sm font-medium shadow-sm transition-colors shrink-0">
            <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            新增顶级菜单
        </a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200">
        <table class="min-w-full text-sm admin-menu-items-table">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-left text-slate-600">
                    <th class="py-3 px-3 font-semibold">标题</th>
                    <th class="py-3 px-3 font-semibold">路由名</th>
                    <th class="py-3 px-3 font-semibold">URL</th>
                    <th class="py-3 px-3 font-semibold">高亮规则</th>
                    <th class="py-3 px-3 font-semibold w-20">排序</th>
                    <th class="py-3 px-3 font-semibold w-24">状态</th>
                    <th class="py-3 px-3 font-semibold min-w-[18rem]">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
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
