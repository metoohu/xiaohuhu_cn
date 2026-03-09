@extends('front.layouts.master')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <article class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 md:p-8">
                    <h1 class="text-2xl md:text-3xl font-bold text-dark-900 mb-4">{{ $article->title }}</h1>
                    <div class="flex flex-wrap gap-4 text-slate-500 text-sm mb-6">
                        @if($article->category)
                        <a href="{{ route('front.categories.show', $article->category) }}" class="text-primary-600 hover:text-primary-700 font-medium">{{ $article->category->name }}</a>
                        @else
                        <span class="text-slate-500">未分类</span>
                        @endif
                        <span>{{ $article->created_at->format('Y-m-d H:i') }}</span>
                        <span>閱讀 {{ $article->click_num }}</span>
                    </div>
                    @if($article->cover_image)
                    <div class="rounded-xl overflow-hidden mb-8 bg-slate-100">
                        <img data-src="{{ \Illuminate\Support\Facades\Storage::url($article->cover_image) }}" alt="{{ $article->title }}" class="w-full lazyload" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                    </div>
                    @endif
                    <div class="prose prose-slate max-w-none prose-headings:text-dark-900 prose-a:text-primary-600 prose-img:rounded-lg mb-8">{!! $article->content !!}</div>

                    <div class="flex justify-between pt-6 border-t border-slate-100 text-sm">
                        <div>
                            @if($prevArticle)
                            <a href="{{ route('front.articles.show', $prevArticle) }}" class="text-primary-600 hover:text-primary-700 font-medium">← {{ Str::limit($prevArticle->title, 28) }}</a>
                            @else
                            <span class="text-slate-400">没有上一篇了</span>
                            @endif
                        </div>
                        <div class="text-right">
                            @if($nextArticle)
                            <a href="{{ route('front.articles.show', $nextArticle) }}" class="text-primary-600 hover:text-primary-700 font-medium">{{ Str::limit($nextArticle->title, 28) }} →</a>
                            @else
                            <span class="text-slate-400">没有下一篇了</span>
                            @endif
                        </div>
                    </div>
                </div>
            </article>

            {{-- 评论区 --}}
            <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 mt-8">
                <h2 class="text-xl font-bold text-dark-900 mb-6">评论区 ({{ $comments->count() }})</h2>

                <form id="comment-form" class="mb-8" x-data="{ submitting: false }">
                    @csrf
                    <input type="hidden" name="article_id" value="{{ $article->id }}">
                    <div class="mb-4">
                        <textarea name="content" rows="4" class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition" placeholder="请输入评论内容..." required></textarea>
                    </div>
                    <button type="submit" :disabled="submitting" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium disabled:opacity-50 transition-colors">
                        <span x-show="!submitting">提交评论</span>
                        <span x-show="submitting" x-cloak>提交中...</span>
                    </button>
                    <p id="comment-message" class="mt-2 text-sm hidden"></p>
                </form>

                @if($comments->isNotEmpty())
                <div class="space-y-5">
                    @foreach($comments as $comment)
                    <div class="pb-5 border-b border-slate-100 last:border-0">
                        <div class="flex justify-between mb-2">
                            <span class="font-medium text-dark-900">{{ $comment->author_name ?: ($comment->user?->name ?? '游客') }}</span>
                            <span class="text-slate-500 text-sm">{{ $comment->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                        <div class="text-slate-700">{{ $comment->content }}</div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-slate-500">暂无评论，快来抢沙发吧！</p>
                @endif
            </section>
        </div>

        {{-- 側邊欄 --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6 sticky top-24">
                <h3 class="font-bold text-dark-900 mb-4 pb-3 border-b border-slate-100">文章分类</h3>
                <ul class="space-y-2">
                    @foreach($categories as $c)
                    <li>
                        <a href="{{ route('front.categories.show', $c) }}" class="flex justify-between py-2 text-slate-600 hover:text-primary-600 transition-colors">
                            <span>{{ $c->name }}</span>
                            <span class="text-slate-400 text-sm">({{ $c->articles_count }})</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-bold text-dark-900 mb-4 pb-3 border-b border-slate-100">热门文章</h3>
                <ul class="space-y-3">
                    @foreach($hotArticles as $a)
                    <li>
                        <a href="{{ route('front.articles.show', $a) }}" class="text-slate-600 hover:text-primary-600 line-clamp-2 transition-colors">{{ $a->title }}</a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('comment-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var btn = form.querySelector('button[type="submit"]');
    var msgEl = document.getElementById('comment-message');
    if (!msgEl) return;
    btn.disabled = true;
    msgEl.textContent = '提交中...';
    msgEl.classList.remove('hidden');
    msgEl.classList.remove('text-green-600', 'text-red-600');

    var fd = new FormData(form);

    fetch('{{ route("front.comments.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        msgEl.textContent = data.message || '提交成功';
        msgEl.classList.add(data.message && data.message.includes('失败') ? 'text-red-600' : 'text-green-600');
        if (data.message && !data.message.includes('失败')) {
            form.querySelector('textarea[name="content"]').value = '';
            setTimeout(function() { location.reload(); }, 1500);
        }
    })
    .catch(function() { msgEl.textContent = '提交失败，请重试'; msgEl.classList.add('text-red-600'); })
    .finally(function() { btn.disabled = false; });
});
</script>
@endpush
@endsection
