@extends('admin.layouts.master')

@section('title', '导入违禁词 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-lg">
    <h2 class="text-xl font-bold mb-4">Excel 导入违禁词</h2>
    <p class="text-sm text-slate-600 mb-4">
        模板列：<code class="text-xs bg-slate-100 px-1 rounded">category_slug</code>、
        <code class="text-xs bg-slate-100 px-1 rounded">word</code>、
        <code class="text-xs bg-slate-100 px-1 rounded">match_type</code>、
        <code class="text-xs bg-slate-100 px-1 rounded">replacement</code>、
        <code class="text-xs bg-slate-100 px-1 rounded">is_enabled</code>、
        <code class="text-xs bg-slate-100 px-1 rounded">remark</code>。
        分类 slug 示例：<code class="text-xs">compliance_redline</code>、<code class="text-xs">tone_violation</code>。
    </p>

    @if(session('warning'))
        <div class="mb-4 p-3 rounded bg-amber-50 text-amber-900 border border-amber-200 text-sm">{{ session('warning') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.forbidden-words.import.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">选择文件</label>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="w-full rounded border-slate-300">
            @error('file')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700">开始导入</button>
            <a href="{{ route('admin.forbidden-words.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">返回列表</a>
        </div>
    </form>
</div>
@endsection
