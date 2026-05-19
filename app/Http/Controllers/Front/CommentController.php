<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Support\CommentContentFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            $returnUrl = $request->input('return_url') ?: $request->headers->get('referer', url()->current());
            $registerUrl = route('front.register', ['return_url' => $returnUrl]);

            return response()->json([
                'message' => '请先登录或注册后再评论',
                'redirect_url' => $registerUrl,
                'require_auth' => true,
            ], 401);
        }

        $user = auth()->user();
        if ($user->isDisabled()) {
            return response()->json([
                'message' => '您的账号已被禁用，无法发表评论',
            ], 403);
        }
        if ($user->isCommentBanned()) {
            return response()->json([
                'message' => '您已被禁言，暂时无法发表评论'.($user->comment_ban_reason ? '：'.$user->comment_ban_reason : ''),
            ], 403);
        }

        $request->validate([
            'article_id' => ['nullable', 'integer', 'exists:articles,id', 'prohibits:user_article_id'],
            'user_article_id' => ['nullable', 'integer', 'exists:user_articles,id', 'prohibits:article_id'],
            'content' => 'required|string|max:2000',
        ]);

        if (! $request->filled('article_id') && ! $request->filled('user_article_id')) {
            return response()->json(['message' => '请指定文章或社区稿'], 422);
        }

        $stickerErr = CommentContentFormatter::validateUserStickers($request->input('content', ''), (int) auth()->id());
        if ($stickerErr !== null) {
            return response()->json(['message' => $stickerErr], 422);
        }

        if (! \App\Models\Setting::get('comment_enabled', '1')) {
            return response()->json(['message' => '评论已关闭'], 403);
        }

        if ($request->filled('article_id')) {
            $article = \App\Models\Article::findOrFail((int) $request->article_id);
            if ($article->status !== 'published') {
                return response()->json(['message' => '文章不存在或已关闭'], 404);
            }

            Comment::create([
                'article_id' => (int) $request->article_id,
                'user_article_id' => null,
                'user_id' => auth()->id(),
                'author_name' => auth()->user()->name,
                'author_email' => auth()->user()->email,
                'content' => $request->content,
                'status' => 'pending',
            ]);
        } else {
            $ua = \App\Models\UserArticle::findOrFail((int) $request->user_article_id);
            if ($ua->status !== \App\Models\UserArticle::STATUS_PUBLISHED || $ua->published_at === null) {
                return response()->json(['message' => '社区稿不存在或未发布'], 404);
            }

            Comment::create([
                'article_id' => null,
                'user_article_id' => $ua->id,
                'user_id' => auth()->id(),
                'author_name' => auth()->user()->name,
                'author_email' => auth()->user()->email,
                'content' => $request->content,
                'status' => 'pending',
            ]);
        }

        return response()->json(['message' => '评论已提交，待审核后显示']);
    }
}
