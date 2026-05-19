@extends('admin.layouts.master')

@section('title', '用户投稿 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">用户投稿</h2>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="标题" class="rounded border-slate-300">
        <select name="status" class="rounded border-slate-300">
            <option value="all" {{ request('status', 'pending_review') === 'all' ? 'selected' : '' }}>全部状态</option>
            <option value="pending_review" {{ request('status', 'pending_review') === 'pending_review' ? 'selected' : '' }}>待审核</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>草稿</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>已发布</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>已驳回</option>
        </select>
        <select name="per_page" class="rounded border-slate-300" onchange="this.form.submit()">
            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>每页 10 条</option>
            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>每页 20 条</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">搜索</button>
    </form>

    <form id="uaBatchForm" method="POST" class="mb-4 flex flex-wrap items-end gap-2">
        @csrf
        <div>
            <label class="block text-xs text-slate-500 mb-1">批量驳回原因（驳回时必填）</label>
            <input type="text" name="rejection_reason" placeholder="驳回原因" class="rounded border-slate-300 text-sm py-1 px-2 w-64">
        </div>
        <button type="submit" formaction="{{ route('admin.user-articles.batch-approve') }}" class="px-3 py-1.5 bg-green-100 text-green-800 rounded text-sm hover:bg-green-200 h-fit">批量通过</button>
        <button type="submit" formaction="{{ route('admin.user-articles.batch-reject') }}" class="px-3 py-1.5 bg-amber-100 text-amber-800 rounded text-sm hover:bg-amber-200 h-fit">批量驳回</button>
    </form>

    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2 w-10"><input type="checkbox" id="uaSelectAll"></th>
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">标题</th>
                <th class="text-left py-2">分类</th>
                <th class="text-left py-2">状态</th>
                <th class="text-left py-2">会员</th>
                <th class="text-left py-2">提交时间</th>
                <th class="text-left py-2">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($articles as $a)
            <tr class="border-b border-slate-100">
                <td class="py-2">
                    @if($a->status === \App\Models\UserArticle::STATUS_PENDING_REVIEW)
                    <input type="checkbox" form="uaBatchForm" name="ids[]" value="{{ $a->id }}" class="ua-cb">
                    @endif
                </td>
                <td class="py-2">{{ $a->id }}</td>
                <td class="py-2">{{ Str::limit($a->title, 36) }}</td>
                <td class="py-2">{{ $a->category?->name ?? '-' }}</td>
                <td class="py-2">
                    @if ($a->status === 'published') <span class="text-green-600">已发布</span>
                    @elseif ($a->status === 'draft') <span class="text-slate-500">草稿</span>
                    @elseif ($a->status === 'pending_review') <span class="text-amber-600">待审核</span>
                    @else <span class="text-red-600">已驳回</span>
                    @endif
                </td>
                <td class="py-2">{{ $a->user?->name ?? '-' }}</td>
                <td class="py-2">{{ $a->submitted_at?->format('Y-m-d H:i') ?? '-' }}</td>
                <td class="py-2">
                    <div class="admin-table-actions">
                        <a href="{{ route('admin.user-articles.show', $a) }}" class="admin-btn-action admin-btn-action--primary">查看</a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $articles->links() }}</div>
</div>
<script>
document.getElementById('uaSelectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.ua-cb').forEach(function (el) { el.checked = document.getElementById('uaSelectAll').checked; });
});
document.getElementById('uaBatchForm')?.addEventListener('submit', function (e) {
    var action = e.submitter && e.submitter.getAttribute('formaction') || '';
    var n = this.querySelectorAll('input[name="ids[]"]:checked').length;
    if (n === 0) {
        e.preventDefault();
        alert('请先勾选待审核稿件');
        return;
    }
    if (action.indexOf('batch-reject') !== -1) {
        var r = (this.querySelector('input[name="rejection_reason"]') || {}).value || '';
        if (!r.trim()) {
            e.preventDefault();
            alert('批量驳回请填写原因');
        }
    }
});
</script>
@endsection
