@extends('admin.layouts.master')

@section('title', '前台会员 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-4">前台注册会员</h2>
    <p class="text-sm text-slate-500 mb-4">管理网站注册用户的<strong>评论禁言</strong>：禁言后仍可登录，但不可发表评论。下方可筛选、批量禁言或<strong>批量解除禁言</strong>（先勾选表格左侧复选框）。「用户管理」为后台管理员账号。</p>

    <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50/80 p-3 sm:p-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-center lg:gap-x-5 lg:gap-y-2">
            {{-- 筛选：单行等高，无顶栏标签 --}}
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="关键词：昵称或邮箱" aria-label="关键词（昵称或邮箱）" class="h-9 min-w-[10rem] flex-1 rounded-md border border-slate-300 px-3 text-sm sm:max-w-[14rem] sm:flex-none">
                <select name="banned" aria-label="评论状态" class="h-9 rounded-md border border-slate-300 bg-white px-2 text-sm">
                    <option value="">全部状态</option>
                    <option value="1" @selected(request('banned') === '1')>已禁言</option>
                    <option value="0" @selected(request('banned') === '0')>未禁言</option>
                </select>
                <button type="submit" class="h-9 shrink-0 rounded-md bg-slate-200 px-4 text-sm font-medium hover:bg-slate-300">搜索</button>
            </form>

            @if($members->total() > 0)
            <div class="hidden lg:block h-8 w-px shrink-0 bg-slate-200" aria-hidden="true"></div>

            {{-- 批量：两按钮紧邻；原因输入与按钮组同排，不因 flex-1 把两表单扯开 --}}
            <div class="flex flex-col gap-2 border-t border-slate-200 pt-3 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-3 sm:gap-y-2 sm:border-t-0 sm:pt-0 lg:flex-1 lg:min-w-0">
                <span class="text-sm text-slate-600 whitespace-nowrap sm:shrink-0">已选 <strong id="member-selected-count" class="tabular-nums text-slate-900">0</strong></span>
                <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
                    <form id="form-batch-mute" action="{{ route('admin.members.batch-mute') }}" method="POST" class="flex items-center gap-2" onsubmit="return memberBatchPrepareSubmit(this, 'mute');">
                        @csrf
                        <input type="text" name="comment_ban_reason" maxlength="500" placeholder="批量禁言原因（选填，统一写入）" aria-label="批量禁言原因" class="h-9 w-48 max-w-full rounded-md border border-slate-300 px-3 text-sm sm:w-56">
                        <button type="submit" class="h-9 shrink-0 rounded-md bg-red-600 px-3 text-sm font-medium text-white hover:bg-red-700">批量禁言</button>
                    </form>
                    <form id="form-batch-unmute" action="{{ route('admin.members.batch-unmute') }}" method="POST" class="flex shrink-0 items-center" onsubmit="return memberBatchPrepareSubmit(this, 'unmute');">
                        @csrf
                        <button type="submit" class="h-9 rounded-md bg-green-600 px-3 text-sm font-medium text-white hover:bg-green-700 whitespace-nowrap">批量解除禁言</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
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
