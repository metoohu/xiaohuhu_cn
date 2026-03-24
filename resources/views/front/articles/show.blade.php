@extends('front.layouts.master')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <article class="bg-white rounded-2xl border border-haze-200 overflow-hidden shadow-sm">
                <div class="p-6 md:p-10">
                    <h1 class="text-2xl md:text-3xl font-serif font-semibold text-primary-800 mb-4">{{ $article->title }}</h1>
                    <div class="flex flex-wrap gap-4 text-dark-800/60 text-sm mb-6">
                        @if($article->category && $article->category->slug)
                        <a href="{{ route('front.categories.show', $article->category) }}" class="text-primary-600 hover:text-primary-700 font-medium">{{ $article->category->name }}</a>
                        @else
                        <span class="text-dark-800/50">未分类</span>
                        @endif
                        <span>{{ $article->created_at->format('Y-m-d H:i') }}</span>
                        <span>阅读 {{ $article->click_num ?? 0 }}</span>
                    </div>
                    @if($article->cover_image)
                    <div class="rounded-xl overflow-hidden mb-8 bg-haze-100">
                        <img data-src="{{ \Illuminate\Support\Facades\Storage::url($article->cover_image) }}" alt="{{ $article->title }}" class="w-full lazyload" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                    </div>
                    @endif
                    <div class="prose prose-lg max-w-none prose-headings:font-serif prose-headings:text-primary-800 prose-p:text-dark-800/80 prose-a:text-primary-600 prose-img:rounded-lg mb-8">{!! $article->content !!}</div>

                    <div class="flex justify-between pt-6 border-t border-haze-200 text-sm">
                        <div>
                            @if($prevArticle)
                            <a href="{{ route('front.articles.show', $prevArticle) }}" class="text-primary-600 hover:text-primary-700 font-medium">← {{ Str::limit($prevArticle->title, 28) }}</a>
                            @else
                            <span class="text-dark-800/40">没有上一篇了</span>
                            @endif
                        </div>
                        <div class="text-right">
                            @if($nextArticle)
                            <a href="{{ route('front.articles.show', $nextArticle) }}" class="text-primary-600 hover:text-primary-700 font-medium">{{ Str::limit($nextArticle->title, 28) }} →</a>
                            @else
                            <span class="text-dark-800/40">没有下一篇了</span>
                            @endif
                        </div>
                    </div>
                </div>
            </article>

            {{-- 评论区 --}}
            <section class="bg-white rounded-2xl border border-haze-200 p-6 md:p-8 mt-8 shadow-sm">
                <h2 class="text-xl font-serif font-semibold text-primary-800 mb-6">评论区 ({{ $comments->count() }})</h2>

                @auth
                <form id="comment-form" class="mb-8" x-data="commentFormState()" x-init="loadMyStickers()" @submit.prevent="submitComment()">
                    @csrf
                    <input type="hidden" name="article_id" value="{{ $article->id }}">
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
                        <p class="text-xs text-dark-800/50 mb-2">点击插入到光标处</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['😀','😃','😄','😁','😅','😂','🤣','😊','😇','🙂','😉','😍','🥰','😘','😋','😎','🤩','🥳','😢','😭','😤','🤔','👍','👏','🙏','❤️','💕','✨','🌟','🔥','🌸','🍀','☕','🎉'] as $emo)
                            <button type="button" @click="insertText(@js($emo))" class="text-xl leading-none p-1 rounded hover:bg-white transition-colors">{{ $emo }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div x-show="stickerOpen" x-cloak x-transition class="mb-3 p-3 rounded-xl border border-haze-200 bg-haze-50/80 max-h-48 overflow-y-auto">
                        <template x-if="stickers.length === 0">
                            <p class="text-sm text-dark-800/60">暂无自定义表情，<a href="{{ route('front.my.stickers') }}" class="text-primary-600 hover:underline">去上传</a></p>
                        </template>
                        <template x-if="stickers.length > 0">
                            <div>
                                <p class="text-xs text-dark-800/50 mb-2">点击插入表情（仅本人上传的可用）</p>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="s in stickers" :key="s.id">
                                        <button type="button" @click="insertSticker(s.id)" class="w-12 h-12 p-1 rounded-lg border border-haze-200 bg-white hover:border-primary-400 transition-colors">
                                            <img :src="s.url" alt="" class="w-full h-full object-contain">
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="mb-4">
                        <textarea id="comment-content" name="content" rows="4" class="w-full rounded-xl border border-haze-200 px-4 py-3 focus:ring-2 focus:ring-primary-500/50 focus:border-primary-400 outline-none transition bg-haze-50/50 resize-y" placeholder="写下你的想法..." required></textarea>
                    </div>
                    <button type="submit" :disabled="submitting" class="px-6 py-2.5 bg-primary-500 text-white rounded-xl hover:bg-primary-600 font-medium disabled:opacity-50 transition-colors shadow-sm">
                        <span x-show="!submitting">提交评论</span>
                        <span x-show="submitting" x-cloak>提交中...</span>
                    </button>
                    <p id="comment-message" class="mt-2 text-sm hidden"></p>
                </form>
                @else
                <div class="mb-8 p-6 rounded-xl bg-haze-50/80 border border-haze-200 text-center">
                    <p class="text-dark-800/70 mb-4">登录或注册后可参与评论</p>
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="{{ route('front.register', ['return_url' => url()->current()]) }}" class="inline-flex items-center px-5 py-2.5 bg-primary-500 text-white rounded-xl hover:bg-primary-600 font-medium transition-colors">注册</a>
                        <a href="{{ route('front.login', ['return_url' => url()->current()]) }}" class="inline-flex items-center px-5 py-2.5 border border-primary-500 text-primary-600 rounded-xl hover:bg-primary-50 font-medium transition-colors">登录</a>
                    </div>
                </div>
                @endauth

                @if($comments->isNotEmpty())
                <div class="space-y-5">
                    @foreach($comments as $comment)
                    <div class="pb-5 border-b border-haze-200 last:border-0">
                        <div class="flex justify-between mb-2">
                            <span class="font-medium text-primary-800">{{ $comment->author_name ?: ($comment->user?->name ?? '游客') }}</span>
                            <span class="text-dark-800/50 text-sm">{{ $comment->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                        <div class="text-dark-800/80 break-words">{!! $comment->content_html !!}</div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-dark-800/50">暂无评论，快来抢沙发吧！</p>
                @endif
            </section>
        </div>

        {{-- 側邊欄 --}}
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

@push('scripts')
<script>
function commentFormState() {
    return {
        submitting: false,
        emojiOpen: false,
        stickerOpen: false,
        stickers: [],
        toggleEmoji() {
            this.emojiOpen = !this.emojiOpen;
            if (this.emojiOpen) this.stickerOpen = false;
        },
        toggleStickers() {
            this.stickerOpen = !this.stickerOpen;
            if (this.stickerOpen) this.emojiOpen = false;
        },
        loadMyStickers() {
            fetch('{{ route("front.my.stickers.json") }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(r) { return r.json(); }).then(function(d) {
                this.stickers = d.stickers || [];
            }.bind(this)).catch(function() {});
        },
        insertText(str) {
            var ta = document.getElementById('comment-content');
            if (!ta) return;
            var start = ta.selectionStart, end = ta.selectionEnd;
            var v = ta.value;
            ta.value = v.slice(0, start) + str + v.slice(end);
            ta.focus();
            var pos = start + str.length;
            ta.setSelectionRange(pos, pos);
        },
        insertSticker(id) {
            this.insertText('[:sticker:' + id + ']');
        },
        submitComment() {
            var form = document.getElementById('comment-form');
            var msgEl = document.getElementById('comment-message');
            if (!form || !msgEl) return;
            this.submitting = true;
            msgEl.textContent = '提交中...';
            msgEl.classList.remove('hidden', 'text-green-600', 'text-red-600');
            var fd = new FormData(form);
            var self = this;
            fetch('{{ route("front.comments.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            })
            .then(function(r) {
                if (r.status === 401) {
                    return r.json().then(function(data) {
                        if (data.redirect_url) window.location.href = data.redirect_url;
                        else window.location.href = '{{ route("front.register", ["return_url" => url()->current()]) }}';
                    });
                }
                return r.json().then(function(data) {
                    return { ok: r.ok, data: data };
                });
            })
            .then(function(res) {
                if (!res || !res.data) return;
                var data = res.data;
                var msg = data.message || '';
                var fail = !res.ok || /失败|无效|最多|关闭/.test(msg);
                msgEl.textContent = msg || (res.ok ? '提交成功' : '提交失败');
                msgEl.classList.add(fail ? 'text-red-600' : 'text-green-600');
                if (res.ok && !fail) {
                    form.querySelector('textarea[name="content"]').value = '';
                    setTimeout(function() { location.reload(); }, 1500);
                }
            })
            .catch(function() {
                msgEl.textContent = '提交失败，请重试';
                msgEl.classList.add('text-red-600');
            })
            .finally(function() { self.submitting = false; });
        }
    };
}
</script>
@endpush
@endsection
