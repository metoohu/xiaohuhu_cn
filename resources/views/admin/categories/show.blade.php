@extends('admin.layouts.master')

@section('title', '分类详情 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">分类详情</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <dt class="text-slate-500">ID</dt><dd>{{ $category->id }}</dd>
        <dt class="text-slate-500">名称</dt><dd>{{ $category->name }}</dd>
        <dt class="text-slate-500">父级</dt><dd>{{ $category->parent?->name ?? '-' }}</dd>
        <dt class="text-slate-500">排序</dt><dd>{{ $category->sort }}</dd>
        <dt class="text-slate-500">描述</dt><dd>{{ $category->description ?? '-' }}</dd>
    </dl>
    <div class="mt-4">
        <a href="{{ route('admin.categories.edit', $category) }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">编辑</a>
        <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300 text-sm ml-2">返回</a>
    </div>
</div>
@endsection
