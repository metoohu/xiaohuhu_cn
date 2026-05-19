{{-- 我的文章列表（可嵌入个人中心或独立页） --}}
@php
    $articleTab = $articleTab ?? 'all';
    $tabs = ['all' => '全部', 'draft' => '草稿', 'pending_review' => '待审核', 'published' => '已发布', 'rejected' => '已驳回'];
    $listBaseQuery = ! empty($listInProfile) ? ['page' => 'articles'] : [];
@endphp

<div class="flex flex-wrap justify-between items-center gap-3 mb-3">
    <p class="text-[13px] text-[#888] px-1">社区投稿</p>
    <a href="{{ route('front.my.articles.create') }}"
       class="px-4 py-2 rounded-lg bg-primary-500 text-white text-[14px] font-medium active:opacity-90 hover:bg-primary-600 shadow-sm">
        写文章
    </a>
</div>

<div class="flex flex-wrap gap-1.5 mb-3 px-0.5">
    @foreach($tabs as $key => $label)
    @php
        $query = $listBaseQuery;
        if ($key !== 'all') {
            $query['article_tab'] = $key;
        }
        $isActive = ($articleTab === $key || ($key === 'all' && ($articleTab === 'all' || $articleTab === '')));
    @endphp
    <a href="{{ route(! empty($listInProfile) ? 'front.my.profile' : 'front.my.articles', $query) }}"
       class="px-2.5 py-1 rounded-full text-[12px] border {{ $isActive ? 'border-primary-500 bg-primary-50 text-primary-800 font-medium' : 'border-[#e5e5e5] text-[#666] bg-white' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="space-y-2">
    @forelse($articles as $a)
    <div class="rounded-xl bg-white shadow-sm shadow-black/[0.03] p-4">
        <div class="flex flex-wrap justify-between gap-2 items-start">
            <h2 class="text-[15px] font-medium text-[#111] leading-snug">{{ $a->title }}</h2>
            <span class="text-[11px] px-2 py-0.5 rounded bg-[#f5f5f5] text-[#666] shrink-0">
                @if($a->status === 'published') 已发布
                @elseif($a->status === 'draft') 草稿
                @elseif($a->status === 'pending_review') 待审核
                @else 已驳回
                @endif
            </span>
        </div>
        <p class="text-[12px] text-[#999] mt-1.5">{{ $a->category?->name }} · {{ $a->updated_at->format('Y-m-d H:i') }}</p>
        @if($a->tags->isNotEmpty())
        <p class="text-[12px] text-primary-600/80 mt-1">{{ $a->tags->pluck('name')->join(' · ') }}</p>
        @endif
        @if($a->status === 'rejected' && $a->rejection_reason)
        <p class="text-[12px] text-amber-800 mt-2">驳回：{{ $a->rejection_reason }}</p>
        @endif
        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-[13px]">
            @if($a->status === 'published')
            <a href="{{ route('front.community.show', $a) }}" class="text-[#576b95]">查看线上</a>
            @endif
            @if($a->status !== 'pending_review')
            <a href="{{ route('front.my.articles.edit', $a) }}" class="text-[#576b95]">编辑</a>
            @endif
            @if($a->status === 'pending_review')
            <form action="{{ route('front.my.articles.withdraw', $a) }}" method="POST" class="inline">@csrf<button type="submit" class="text-amber-700">撤回</button></form>
            @endif
            @if(in_array($a->status, ['draft','rejected'], true))
            <form action="{{ route('front.my.articles.destroy', $a) }}" method="POST" class="inline" onsubmit="return confirm('确定删除？');">@csrf @method('DELETE')<button type="submit" class="text-[#fa5151]">删除</button></form>
            <form action="{{ route('front.my.articles.submit', $a) }}" method="POST" class="inline">@csrf<button type="submit" class="text-[#576b95]">提交审核</button></form>
            @endif
        </div>
    </div>
    @empty
    <p class="text-center text-[13px] text-[#999] py-10 bg-white rounded-xl">暂无稿件，<a href="{{ route('front.my.articles.create') }}" class="text-[#576b95]">去写一篇</a></p>
    @endforelse
</div>

@if($articles->hasPages())
<div class="mt-4 text-[13px]">{{ $articles->links() }}</div>
@endif
