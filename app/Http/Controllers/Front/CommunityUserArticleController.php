<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\UserArticle;
use Illuminate\View\View;

/**
 * 前台公共读：已发布用户社区稿详情（仅 content_public）。
 */
class CommunityUserArticleController extends Controller
{
    public function show(UserArticle $userArticle): View
    {
        if ($userArticle->status !== UserArticle::STATUS_PUBLISHED || $userArticle->published_at === null) {
            abort(404);
        }

        $userArticle->load(['category', 'tags', 'user']);
        $userArticle->increment('click_num');

        $comments = Comment::query()
            ->where('user_article_id', $userArticle->id)
            ->where('status', Comment::STATUS_APPROVED)
            ->whereNull('parent_id')
            ->with(['user', 'replies' => fn ($q) => $q->where('status', Comment::STATUS_APPROVED)->with('user')])
            ->latest()
            ->get();

        $categories = Category::enabled()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->get();

        $hotArticles = Article::where('status', 'published')->orderByDesc('click_num')->take(8)->get();

        $seo = [
            'title' => $userArticle->title.' - '.\App\Models\Setting::get('site_title', \App\Models\Setting::adminName()),
            'keywords' => \App\Models\Setting::seoKeywords(),
            'description' => $userArticle->excerpt ?: \App\Models\Setting::seoDescription(),
        ];

        return view('front.community.show', compact('userArticle', 'comments', 'categories', 'hotArticles', 'seo'));
    }
}
