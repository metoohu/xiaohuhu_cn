@extends('admin.layouts.master')

@section('title', '批量导入文章 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-2xl">
    <h2 class="text-xl font-bold mb-4">Excel 批量导入文章</h2>
    <p class="text-sm text-slate-600 mb-4">
        模板列：
        <code class="text-xs bg-slate-100 px-1 rounded">title</code>、
        <code class="text-xs bg-slate-100 px-1 rounded">content</code>、
        <code class="text-xs bg-slate-100 px-1 rounded">excerpt</code>、
        <code class="text-xs bg-slate-100 px-1 rounded">seo_title</code>、
        <code class="text-xs bg-slate-100 px-1 rounded">seo_keywords</code>、
        <code class="text-xs bg-slate-100 px-1 rounded">seo_description</code>、
        <code class="text-xs bg-slate-100 px-1 rounded">category_id</code>。
        每行导入前会执行违禁词校验，违规行不入库。
    </p>

    <p class="mb-4">
        <a href="{{ route('admin.articles.import.template') }}" class="text-sm text-slate-700 underline hover:text-slate-900">下载 Excel 模板</a>
    </p>

    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-50 text-green-900 border border-green-200 text-sm">{{ session('success') }}</div>
    @endif

    @php $importReport = session('import_report'); @endphp
    @if(is_array($importReport) && ! empty($importReport['failed']))
        <div class="mb-4 p-3 rounded bg-amber-50 text-amber-950 border border-amber-200 text-sm">
            <p class="font-medium mb-2">失败行明细</p>
            <ul class="space-y-1 list-disc list-inside">
                @foreach($importReport['failed'] as $item)
                <li>
                    第 {{ $item['row'] ?? '?' }} 行：
                    {{ implode('；', $item['messages'] ?? []) }}
                </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.articles.import.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">选择文件</label>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="w-full rounded border-slate-300">
            @error('file')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700">开始导入</button>
            <a href="{{ route('admin.articles.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">返回列表</a>
        </div>
    </form>
</div>
@endsection
