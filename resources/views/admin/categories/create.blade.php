@extends('admin.layouts.master')

@section('title', '新增分类 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-md">
    <h2 class="text-xl font-bold mb-4">新增分类</h2>
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">名称</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">父级分类</label>
                <select name="parent_id" class="w-full rounded border-slate-300">
                    <option value="">无</option>
                    @foreach ($parents as $p)
                        <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">排序</label>
                <input type="number" name="sort" value="{{ old('sort', 0) }}" min="0" class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">描述</label>
                <textarea name="description" rows="3" class="w-full rounded border-slate-300">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">图标/封面图</label>
                <input type="file" name="icon" accept="image/*" class="w-full rounded border-slate-300">
                <p class="text-xs text-slate-500 mt-1">支持 jpg、png、gif，最大 2MB</p>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700">创建</button>
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">取消</a>
        </div>
    </form>
</div>
@endsection
