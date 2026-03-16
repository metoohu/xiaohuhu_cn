<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Comment;
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

        $request->validate([
            'article_id' => 'required|exists:articles,id',
            'content' => 'required|string|max:2000',
        ]);

        $article = \App\Models\Article::findOrFail($request->article_id);
        if ($article->status !== 'published') {
            return response()->json(['message' => '文章不存在或已关闭'], 404);
        }

        if (! \App\Models\Setting::get('comment_enabled', '1')) {
            return response()->json(['message' => '评论已关闭'], 403);
        }

        Comment::create([
            'article_id' => $request->article_id,
            'user_id' => auth()->id(),
            'author_name' => auth()->user()->name,
            'author_email' => auth()->user()->email,
            'content' => $request->content,
            'status' => 'pending',
        ]);

        return response()->json(['message' => '评论已提交，待审核后显示']);
    }
}
