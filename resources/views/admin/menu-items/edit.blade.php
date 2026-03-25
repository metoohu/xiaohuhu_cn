@extends('admin.layouts.master')

@section('title', '编辑菜单 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-2xl">
    <h2 class="text-xl font-bold mb-4">编辑菜单</h2>

    <form action="{{ route('admin.menu-items.update', $item) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">上级菜单</label>
            <select name="parent_id" class="w-full rounded border-slate-300 text-sm">
                @foreach($parentOptions as $opt)
                <option value="{{ $opt['value'] }}" @selected(old('parent_id', $item->parent_id) == $opt['value'])>{{ $opt['label'] }}</option>
                @endforeach
            </select>
            @error('parent_id')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_divider" value="1" @checked(old('is_divider', $item->is_divider)) class="rounded border-slate-300">
                <span>分隔线（无链接）</span>
            </label>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">标题</label>
            <input type="text" name="title" value="{{ old('title', $item->title) }}" maxlength="100" class="w-full rounded border-slate-300 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">路由名（优先）</label>
            <select name="route_name" class="w-full rounded border-slate-300 text-sm">
                <option value="">— 不绑定 —</option>
                @foreach($routeNames as $rn)
                <option value="{{ $rn }}" @selected(old('route_name', $item->route_name) === $rn)>{{ $rn }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">自定义 URL</label>
            <input type="text" name="url" value="{{ old('url', $item->url) }}" maxlength="500" class="w-full rounded border-slate-300 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">高亮路由规则（可选）</label>
            <input type="text" name="active_pattern" value="{{ old('active_pattern', $item->active_pattern) }}" maxlength="191" class="w-full rounded border-slate-300 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">排序值</label>
            <input type="number" name="sort" value="{{ old('sort', $item->sort) }}" min="0" max="65535" class="w-40 rounded border-slate-300 text-sm">
        </div>

        <div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active)) class="rounded border-slate-300">
                <span>在侧栏显示</span>
            </label>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700">保存</button>
            <a href="{{ route('admin.menu-items.index') }}" class="px-4 py-2 bg-slate-200 rounded-lg hover:bg-slate-300">返回</a>
        </div>
    </form>
</div>
@endsection
