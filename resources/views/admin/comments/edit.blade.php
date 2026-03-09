@extends('admin.layouts.master')

@section('title', '编辑评论 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-md">
    <h2 class="text-xl font-bold mb-4">编辑评论</h2>
    <form method="POST" action="{{ route('admin.comments.update', $comment) }}">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">状态</label>
                <select name="status" class="w-full rounded border-slate-300">
                    <option value="pending" {{ old('status', $comment->status) === 'pending' ? 'selected' : '' }}>待审核</option>
                    <option value="approved" {{ old('status', $comment->status) === 'approved' ? 'selected' : '' }}>已通过</option>
                    <option value="rejected" {{ old('status', $comment->status) === 'rejected' ? 'selected' : '' }}>已拒绝</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">内容</label>
                <textarea name="content" rows="5" required class="w-full rounded border-slate-300">{{ old('content', $comment->content) }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700">更新</button>
            <a href="{{ route('admin.comments.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">取消</a>
        </div>
    </form>
</div>
@endsection
