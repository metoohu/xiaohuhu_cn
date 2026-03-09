@extends('admin.layouts.master')

@section('title', '错误日志 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">错误日志</h2>
        <a href="{{ route('admin.logs.index') }}" class="text-blue-600 hover:underline">返回</a>
    </div>
    <pre class="p-4 bg-slate-900 text-slate-100 rounded text-xs overflow-x-auto whitespace-pre-wrap max-h-[600px] overflow-y-auto">{{ $content ?: '暂无错误日志' }}</pre>
</div>
@endsection
