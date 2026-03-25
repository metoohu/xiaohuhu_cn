@extends('admin.layouts.master')

@section('title', '前台会员 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">前台注册会员</h2>
    <p class="text-sm text-slate-500 mb-4">管理网站注册用户的<strong>评论禁言</strong>：禁言后仍可登录，但不可发表评论。下方同一行可搜索、批量禁言或<strong>批量解除禁言</strong>（先勾选表格左侧复选框）。「用户管理」为后台管理员账号。</p>

    <div class="mb-4 flex flex-wrap items-end gap-x-4 gap-y-3 p-3 rounded-lg border border-slate-200 bg-slate-50/80">
        <form method="GET" class="flex flex-wrap items-end gap-2 shrink-0">
            <div>
                <label class="block text-xs text-slate-500 mb-1">关键词</label>
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="昵称或邮箱" class="rounded border-slate-300 text-sm min-w-[9rem] w-36">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">评论状态</label>
                <select name="banned" class="rounded border-slate-300 text-sm">
                    <option value="">全部</option>
                    <option value="1" @selected(request('banned') === '1')>已禁言</option>
                    <option value="0" @selected(request('banned') === '0')>未禁言</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300 text-sm h-[34px] self-end">搜索</button>
        </form>

        @if($members->total() > 0)
        <div class="hidden sm:block w-px h-9 bg-slate-200 self-end mb-0.5 shrink-0" aria-hidden="true"></div>

        <div class="flex flex-wrap items-end gap-x-3 gap-y-2 flex-1 min-w-0">
            <span class="text-xs text-slate-600 whitespace-nowrap self-end pb-2">已选 <strong id="member-selected-count" class="text-slate-900">0</strong></span>
            <form id="form-batch-mute" action="{{ route('admin.members.batch-mute') }}" method="POST" class="flex flex-wrap items-end gap-2" onsubmit="return memberBatchPrepareSubmit(this, 'mute');">
                @csrf
                <div class="min-w-0">
                    <label class="block text-xs text-slate-500 mb-1">批量禁言</label>
                    <input type="text" name="comment_ban_reason" maxlength="500" placeholder="原因（选填，统一写入）" class="rounded border-slate-300 text-sm w-48 max-w-[min(100vw-2rem,12rem)] sm:max-w-none sm:w-52 h-[34px] px-2">
                </div>
                <button type="submit" class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 h-[34px]">批量禁言</button>
            </form>
            <form id="form-batch-unmute" action="{{ route('admin.members.batch-unmute') }}" method="POST" class="flex flex-col justify-end" onsubmit="return memberBatchPrepareSubmit(this, 'unmute');">
                @csrf
                <span class="block text-xs text-slate-500 mb-1 h-4 leading-4">解除禁言</span>
                <button type="submit" class="px-3 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 h-[34px] whitespace-nowrap">批量解除禁言</button>
            </form>
        </div>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left py-2 pr-2 w-10">
                        @if($members->total() > 0)
                        <input type="checkbox" id="member-check-all" title="全选本页" class="rounded border-slate-300">
                        @endif
                    </th>
                    <th class="text-left py-2 pr-3">ID</th>
                    <th class="text-left py-2 pr-3">昵称</th>
                    <th class="text-left py-2 pr-3">邮箱</th>
                    <th class="text-left py-2 pr-3">评论数</th>
                    <th class="text-left py-2 pr-3">表情数</th>
                    <th class="text-left py-2 pr-3">评论</th>
                    <th class="text-left py-2 pr-3">注册时间</th>
                    <th class="text-left py-2 min-w-[10rem]">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $m)
                <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                    <td class="py-2 pr-2 align-top">
                        <input type="checkbox" class="member-check rounded border-slate-300" value="{{ $m->id }}" data-member-check>
                    </td>
                    <td class="py-2 pr-3">{{ $m->id }}</td>
                    <td class="py-2 pr-3">{{ $m->name }}</td>
                    <td class="py-2 pr-3 break-all max-w-[14rem]">{{ $m->email }}</td>
                    <td class="py-2 pr-3">{{ $m->comments_count }}</td>
                    <td class="py-2 pr-3">{{ $m->stickers_count }}</td>
                    <td class="py-2 pr-3">
                        @if($m->isCommentBanned())
                        <span class="text-red-600 font-medium">已禁言</span>
                        @else
                        <span class="text-green-600">可评论</span>
                        @endif
                    </td>
                    <td class="py-2 pr-3 whitespace-nowrap">{{ $m->created_at->format('Y-m-d H:i') }}</td>
                    <td class="py-2">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <a href="{{ route('admin.members.show', $m) }}" class="text-blue-600 hover:underline shrink-0">查看</a>
                            @if($m->isCommentBanned())
                            <form action="{{ route('admin.members.unmute', $m) }}" method="POST" class="inline" onsubmit="return confirm('确定解除该用户的禁言？');">
                                @csrf
                                <button type="submit" class="text-green-600 hover:underline text-left">解除禁言</button>
                            </form>
                            @else
                            <form action="{{ route('admin.members.mute', $m) }}" method="POST" class="inline" onsubmit="return confirm('确定禁言该用户？禁言后其无法发表评论。');">
                                @csrf
                                <button type="submit" class="text-red-600 hover:underline text-left">禁言</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-8 text-center text-slate-500">暂无会员数据</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($members->total() > 0)
    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2 border-t border-slate-100">
        <p class="text-sm text-slate-600">
            共 <span class="font-semibold text-slate-800">{{ $members->total() }}</span> 位
            @if($members->hasPages())
            ，当前第 <span class="font-medium">{{ $members->currentPage() }}</span> / {{ $members->lastPage() }} 页
            @endif
        </p>
        <div class="pagination-wrap">
            {{ $members->onEachSide(1)->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    function selectedChecks() {
        return Array.prototype.slice.call(document.querySelectorAll('[data-member-check]:checked'));
    }
    function updateCount() {
        var el = document.getElementById('member-selected-count');
        if (el) el.textContent = String(selectedChecks().length);
    }
    window.memberBatchPrepareSubmit = function (form, kind) {
        if (!form) return false;
        var checks = selectedChecks();
        if (!checks.length) {
            alert('请先勾选要操作的会员（当前列表页）');
            return false;
        }
        if (kind === 'mute' && !confirm('确定对所选 ' + checks.length + ' 位用户批量禁言？')) return false;
        if (kind === 'unmute' && !confirm('确定对所选用户批量解除禁言？（仅已禁言的会生效）')) return false;
        form.querySelectorAll('input[name="ids[]"]').forEach(function (n) { n.remove(); });
        checks.forEach(function (c) {
            var h = document.createElement('input');
            h.type = 'hidden';
            h.name = 'ids[]';
            h.value = c.value;
            form.appendChild(h);
        });
        return true;
    };
    var all = document.getElementById('member-check-all');
    if (all) {
        all.addEventListener('change', function () {
            document.querySelectorAll('[data-member-check]').forEach(function (c) { c.checked = all.checked; });
            updateCount();
        });
    }
    document.querySelectorAll('[data-member-check]').forEach(function (c) {
        c.addEventListener('change', function () {
            updateCount();
            var rowChecks = document.querySelectorAll('[data-member-check]');
            var allOn = rowChecks.length && Array.prototype.every.call(rowChecks, function (x) { return x.checked; });
            if (all) all.checked = allOn;
        });
    });
})();
</script>
@endpush
