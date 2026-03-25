@extends('admin.layouts.master')

@section('title', '会员详情 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-3xl">
    <h2 class="text-xl font-bold mb-4">会员详情</h2>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
        <dt class="text-slate-500">ID</dt><dd>{{ $member->id }}</dd>
        <dt class="text-slate-500">昵称</dt><dd>{{ $member->name }}</dd>
        <dt class="text-slate-500">邮箱</dt><dd>{{ $member->email }}</dd>
        <dt class="text-slate-500">注册时间</dt><dd>{{ $member->created_at->format('Y-m-d H:i') }}</dd>
        <dt class="text-slate-500">个性签名</dt><dd class="sm:col-span-1 break-words">{{ $member->signature ?: '—' }}</dd>
        <dt class="text-slate-500">心情</dt><dd>{{ $member->mood_emoji }} {{ $member->mood_text ?: '—' }}</dd>
        <dt class="text-slate-500">生日</dt><dd>{{ $member->birthday?->format('Y-m-d') ?: '—' }}</dd>
        <dt class="text-slate-500">性别</dt><dd>{{ match ($member->gender ?? '') {
            'male' => '男',
            'female' => '女',
            'other' => '其他',
            'secret' => '不公开',
            '' => '—',
            default => $member->gender,
        } }}</dd>
        <dt class="text-slate-500">兴趣爱好</dt><dd class="sm:col-span-1 break-words">{{ $member->interests ?: '—' }}</dd>
        <dt class="text-slate-500">职业</dt><dd>{{ $member->occupation ?: '—' }}</dd>
        <dt class="text-slate-500">评论数</dt><dd>{{ $member->comments_count }}</dd>
        <dt class="text-slate-500">自定义表情</dt><dd>{{ $member->stickers_count }} 个</dd>
        <dt class="text-slate-500">评论状态</dt><dd>
            @if($member->isCommentBanned())
            <span class="text-red-600 font-medium">已禁言</span>
            @if($member->comment_banned_at)
            <span class="text-slate-500">（{{ $member->comment_banned_at->format('Y-m-d H:i') }}）</span>
            @endif
            @else
            <span class="text-green-600">可评论</span>
            @endif
        </dd>
        @if($member->comment_ban_reason)
        <dt class="text-slate-500">禁言原因</dt><dd class="sm:col-span-2 text-red-700">{{ $member->comment_ban_reason }}</dd>
        @endif
    </dl>

    @if($member->avatar)
    <div class="mt-4">
        <p class="text-sm text-slate-500 mb-1">头像</p>
        <img src="{{ \Illuminate\Support\Facades\Storage::url($member->avatar) }}" alt="" class="w-24 h-24 rounded-full object-cover border border-slate-200">
    </div>
    @endif

    <div class="mt-6 flex flex-wrap gap-3">
        @if($member->isCommentBanned())
        <form action="{{ route('admin.members.unmute', $member) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">解除禁言</button>
        </form>
        @else
        <form action="{{ route('admin.members.mute', $member) }}" method="POST" class="inline space-y-2 w-full max-w-md">
            @csrf
            <div>
                <label class="block text-xs text-slate-500 mb-1">禁言原因（选填，用户可见）</label>
                <input type="text" name="comment_ban_reason" class="w-full rounded border-slate-300 text-sm" maxlength="500" placeholder="例如：发布违规内容">
            </div>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm" onclick="return confirm('确定禁言该用户？禁言后其无法发表评论。');">禁言</button>
        </form>
        @endif
        <a href="{{ route('admin.members.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300 text-sm inline-flex items-center">返回列表</a>
    </div>

    @if($recentComments->isNotEmpty())
    <div class="mt-8 pt-6 border-t border-slate-200">
        <h3 class="font-semibold mb-3">最近评论</h3>
        <ul class="space-y-2 text-sm">
            @foreach($recentComments as $c)
            <li class="border-b border-slate-100 pb-2">
                <a href="{{ route('admin.articles.edit', $c->article_id) }}" class="text-blue-600 hover:underline">{{ Str::limit($c->article?->title ?? '文章', 40) }}</a>
                <span class="text-slate-400 mx-1">·</span>
                <span class="text-slate-500">{{ $c->created_at->format('Y-m-d H:i') }}</span>
                <span class="text-slate-400 mx-1">·</span>
                <span class="text-slate-600">{{ Str::limit($c->content, 80) }}</span>
                <a href="{{ route('admin.comments.edit', $c) }}" class="text-blue-600 hover:underline ml-2">编辑</a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
