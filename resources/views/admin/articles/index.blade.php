@extends('admin.layouts.master')

@section('title', '文章管理 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">文章管理</h2>
        <a href="{{ route('admin.articles.create') }}" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 text-sm">新增文章</a>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="标题" class="rounded border-slate-300">
        <select name="status" class="rounded border-slate-300">
            <option value="">全部状态</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>草稿</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>已发布</option>
            <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>待审核</option>
        </select>
        <select name="category_id" class="rounded border-slate-300">
            <option value="">全部分类</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="per_page" class="rounded border-slate-300" onchange="this.form.submit()">
            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>每页 10 条</option>
            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>每页 20 条</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>每页 50 条</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">搜索</button>
    </form>

    <form action="{{ route('admin.articles.batch') }}" method="POST" id="articleBatchForm" class="mb-4 flex flex-wrap items-center gap-2">
        @csrf
        <button type="submit" name="action" value="approve" class="px-3 py-1.5 bg-green-100 text-green-800 rounded text-sm hover:bg-green-200">批量通过</button>
        <button type="submit" name="action" value="reject" class="px-3 py-1.5 bg-amber-100 text-amber-800 rounded text-sm hover:bg-amber-200">批量驳回</button>
        <input type="text" name="review_comment" placeholder="驳回意见（可选）" class="rounded border-slate-300 text-sm py-1 px-2 w-40">
    </form>

    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2 w-10"><input type="checkbox" id="articleSelectAll"></th>
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">标题</th>
                <th class="text-left py-2">分类</th>
                <th class="text-left py-2">状态</th>
                <th class="text-left py-2">作者</th>
                <th class="text-left py-2">时间</th>
                <th class="text-left py-2">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($articles as $a)
            <tr class="border-b border-slate-100">
                <td class="py-2"><input type="checkbox" form="articleBatchForm" name="ids[]" value="{{ $a->id }}" class="article-checkbox"></td>
                <td class="py-2">{{ $a->id }}</td>
                <td class="py-2">{{ Str::limit($a->title, 40) }}</td>
                <td class="py-2">{{ $a->category?->name ?? '-' }}</td>
                <td class="py-2">
                    @if ($a->status === 'published') <span class="text-green-600">已发布</span>
                    @elseif ($a->status === 'draft') <span class="text-slate-500">草稿</span>
                    @else <span class="text-amber-600">待审核</span>
                    @endif
                </td>
                <td class="py-2">{{ $a->adminUser?->name ?? '-' }}</td>
                <td class="py-2">{{ $a->created_at->format('Y-m-d H:i') }}</td>
                <td class="py-2">
                    @if ($a->status === 'review')
                    <a href="{{ route('admin.articles.show', $a) }}" class="text-blue-600 hover:underline">人工审核</a>
                    <span class="text-slate-300 mx-1">|</span>
                    @endif
                    <a href="{{ route('admin.articles.edit', $a) }}" class="text-blue-600 hover:underline">编辑</a>
                    <form action="{{ route('admin.articles.destroy', $a) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline ml-1" onclick="return confirm('确定删除此文章？')">删除</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4 flex flex-wrap justify-between items-center gap-4">
        <div class="text-sm text-slate-500">
            共 {{ $articles->total() }} 条，第 {{ $articles->currentPage() }}/{{ $articles->lastPage() }} 页
        </div>
        <div>{{ $articles->links() }}</div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('articleSelectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.article-checkbox').forEach(cb => cb.checked = this.checked);
});
document.getElementById('articleBatchForm')?.addEventListener('submit', function(e) {
    var btn = e.submitter;
    if (btn && btn.name === 'action') {
        var checked = document.querySelectorAll('.article-checkbox:checked');
        if (checked.length === 0) {
            e.preventDefault();
            alert('请先选择要操作的文章');
        }
    }
});
</script>
@endpush
@endsection
