@extends('front.layouts.master')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <article class="bg-white rounded-2xl border border-haze-200 overflow-hidden shadow-sm">
                <div class="p-6 md:p-10">
                    <p class="text-xs text-primary-600 font-medium mb-2">社区投稿</p>
                    <h1 class="text-2xl md:text-3xl font-serif font-semibold text-primary-800 mb-4">{{ $userArticle->title }}</h1>
                    <div class="flex flex-wrap gap-4 text-dark-800/60 text-sm mb-6">
                        @if($userArticle->category && $userArticle->category->slug)
                        <a href="{{ route('front.categories.show', $userArticle->category) }}" class="text-primary-600 hover:text-primary-700 font-medium">{{ $userArticle->category->name }}</a>
                        @endif
                        <span>{{ $userArticle->published_at?->format('Y-m-d H:i') }}</span>
                        <span>阅读 {{ $userArticle->click_num ?? 0 }}</span>
                        @if($userArticle->user)
                        <span>作者 {{ $userArticle->user->name }}</span>
                        @endif
                    </div>
                    @if($userArticle->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach($userArticle->tags as $t)
                        <span class="text-xs px-2 py-1 rounded-full bg-haze-100 text-dark-800/70">{{ $t->name }}</span>
                        @endforeach
                    </div>
                    @endif
                    <div class="prose prose-lg max-w-none prose-p:text-dark-800/80">{!! $userArticle->displayContentSafeHtml() !!}</div>
                </div>
            </article>

            <section class="bg-white rounded-2xl border border-haze-200 p-6 md:p-8 mt-8 shadow-sm">
                <h2 class="text-xl font-serif font-semibold text-primary-800 mb-6">评论区 ({{ $comments->count() }})</h2>

                {{-- 访客：与 @auth 分支分离，避免 @if 禁言 的 @else 后再跟 @else 触发 ParseError --}}
                @guest
                <p class="text-dark-800/60 text-sm mb-6"><a href="{{ route('front.login', ['return_url' => url()->current()]) }}" class="text-primary-600 hover:underline">登录</a> 后参与评论</p>
                @endguest

                @auth
                @if(auth()->user()->isCommentBanned())
                <div class="mb-8 p-6 rounded-xl bg-amber-50/90 border border-amber-200 text-dark-800/80 text-sm">
                    <p class="font-medium text-amber-900">您已被禁言，暂时无法发表评论。</p>
                </div>
                @else
                <form id="comment-form" class="mb-8" x-data="communityCommentForm()" x-init="loadMyStickers()" @submit.prevent="submitComment()">
                    @csrf
                    <input type="hidden" name="user_article_id" value="{{ $userArticle->id }}">
                    <input type="hidden" name="return_url" value="{{ url()->current() }}">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <button type="button" @click="toggleEmoji()" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded-xl border border-haze-200 bg-white hover:bg-haze-50 text-dark-800/80 transition-colors">
                            <span aria-hidden="true">😊</span> 表情
                        </button>
                        <button type="button" @click="toggleStickers()" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded-xl border border-haze-200 bg-white hover:bg-haze-50 text-dark-800/80 transition-colors">
                            <span aria-hidden="true">🖼</span> 我的表情
                        </button>
                        <a href="{{ route('front.my.stickers') }}" class="text-sm text-primary-600 hover:text-primary-700 ml-1">管理表情包</a>
                    </div>
                    <div x-show="emojiOpen" x-cloak x-transition class="mb-3 p-3 rounded-xl border border-haze-200 bg-haze-50/80 max-h-40 overflow-y-auto">
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['😀','😊','👍','❤️','🙏'] as $emo)
                            <button type="button" @click="insertText(@js($emo))" class="text-xl leading-none p-1 rounded hover:bg-white transition-colors">{{ $emo }}</button>
                            @endforeach
                        </div>
                    </div>
                    <textarea id="comment-content" name="content" rows="4" class="w-full rounded-xl border-haze-200" placeholder="写下你的想法…" required maxlength="2000"></textarea>
                    <p id="comment-message" class="mt-2 text-sm hidden"></p>
                    <button type="submit" class="mt-3 px-5 py-2.5 rounded-xl bg-primary-600 text-white text-sm hover:bg-primary-700">发表评论</button>
                </form>
                @endif
                @endauth

                @if($comments->isNotEmpty())
                <div class="space-y-6">
                    @foreach($comments as $comment)
                    <div class="border-b border-haze-100 pb-6 last:border-0 last:pb-0">
                        <div class="flex gap-3">
                            @if($comment->user?->avatar)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($comment->user->avatar) }}" alt="" class="w-10 h-10 rounded-full object-cover shrink-0 border border-haze-200">
                            @else
                            <div class="w-10 h-10 rounded-full bg-haze-200 shrink-0 flex items-center justify-center text-primary-600 text-sm font-medium">{{ mb_substr($comment->author_name ?: ($comment->user?->name ?? '?'), 0, 1) }}</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap justify-between gap-2 mb-1">
                                    <span class="font-medium text-primary-800">{{ $comment->author_name ?: ($comment->user?->name ?? '游客') }}</span>
                                    <span class="text-dark-800/50 text-sm">{{ $comment->created_at->format('Y-m-d H:i') }}</span>
                                </div>
                                <div class="text-dark-800/80 break-words">{!! $comment->content_html !!}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-dark-800/50">暂无评论，快来抢沙发吧！</p>
                @endif
            </section>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-haze-200 p-6 mb-6 sticky top-24 shadow-sm">
                <h3 class="font-serif font-semibold text-primary-800 mb-4 pb-3 border-b border-haze-200">文章分类</h3>
                <ul class="space-y-2">
                    @foreach($categories as $c)
                    <li>
                        <a href="{{ route('front.categories.show', $c) }}" class="flex justify-between py-2 text-dark-800/70 hover:text-primary-600 transition-colors">
                            <span>{{ $c->name }}</span>
                            <span class="text-haze-500 text-sm">({{ $c->articles_count }})</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="bg-white rounded-2xl border border-haze-200 p-6 shadow-sm">
                <h3 class="font-serif font-semibold text-primary-800 mb-4 pb-3 border-b border-haze-200">热门文章</h3>
                <ul class="space-y-3">
                    @foreach($hotArticles as $a)
                    <li>
                        <a href="{{ route('front.articles.show', $a) }}" class="text-dark-800/70 hover:text-primary-600 line-clamp-2 transition-colors">{{ $a->title }}</a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

@auth
@push('scripts')
<script>
function communityCommentForm() {
    return {
        submitting: false,
        emojiOpen: false,
        stickerOpen: false,
        stickers: [],
        toggleEmoji() { this.emojiOpen = !this.emojiOpen; if (this.emojiOpen) this.stickerOpen = false; },
        toggleStickers() { this.stickerOpen = !this.stickerOpen; if (this.stickerOpen) this.emojiOpen = false; },
        loadMyStickers() {
            fetch('{{ route("front.my.stickers.json") }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
                .then(r => r.json()).then(d => { this.stickers = d.stickers || []; }).catch(() => {});
        },
        insertText(str) {
            var ta = document.getElementById('comment-content');
            if (!ta) return;
            var start = ta.selectionStart, end = ta.selectionEnd, v = ta.value;
            ta.value = v.slice(0, start) + str + v.slice(end);
            ta.focus();
            var pos = start + str.length;
            ta.setSelectionRange(pos, pos);
        },
        submitComment() {
            var form = document.getElementById('comment-form');
            var msgEl = document.getElementById('comment-message');
            if (!form || !msgEl) return;
            this.submitting = true;
            msgEl.textContent = '提交中...';
            msgEl.classList.remove('hidden', 'text-green-600', 'text-red-600');
            var fd = new FormData(form);
            fetch('{{ route("front.comments.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            }).then(r => {
                if (r.status === 401) {
                    return r.json().then(data => { if (data.redirect_url) window.location.href = data.redirect_url; });
                }
                return r.json().then(data => ({ ok: r.ok, data }));
            }).then(res => {
                if (!res || !res.data) return;
                var data = res.data;
                var msg = data.message || '';
                var fail = !res.ok || /失败|无效|最多|关闭|禁言/.test(msg);
                msgEl.textContent = msg || (res.ok ? '提交成功' : '提交失败');
                msgEl.classList.add(fail ? 'text-red-600' : 'text-green-600');
                if (res.ok && !fail) {
                    form.querySelector('textarea[name="content"]').value = '';
                    setTimeout(function () { location.reload(); }, 1200);
                }
            }).catch(() => {
                msgEl.textContent = '提交失败，请重试';
                msgEl.classList.add('text-red-600');
            }).finally(() => { this.submitting = false; });
        }
    };
}
</script>
@endpush
@endauth
@endsection
