<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $cacheKey = 'front_articles_' . md5($request->getQueryString());
        $articles = Cache::remember($cacheKey, config('front.cache_ttl', 30) * 60, function () use ($request) {
            $query = Article::where('status', 'published')
                ->with('category')
                ->orderByDesc('created_at');

            if ($request->category_id) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->keyword) {
                $keyword = '%' . $request->keyword . '%';
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', $keyword)
                        ->orWhere('content', 'like', $keyword);
                });
            }

            $order = $request->get('order', 'time');
            if ($order === 'click') {
                $query->orderByDesc('click_num')->orderByDesc('created_at');
            } else {
                $query->orderByDesc('created_at');
            }

            return $query->paginate(config('front.article_per_page', 15));
        });

        $keyword = $request->keyword ?? '';

        $categories = Category::enabled()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->get();

        $seo = [
            'title' => '文章列表',
            'keywords' => \App\Models\Setting::seoKeywords(),
            'description' => \App\Models\Setting::seoDescription(),
        ];

        return view('front.articles.index', compact('articles', 'categories', 'seo', 'keyword'));
    }

    public function show(Article $article): View
    {
        if ($article->status !== 'published') {
            abort(404);
        }

        $article->increment('click_num');

        $prevArticle = Article::where('id', '<', $article->id)
            ->where('status', 'published')
            ->orderByDesc('id')
            ->first();

        $nextArticle = Article::where('id', '>', $article->id)
            ->where('status', 'published')
            ->orderBy('id')
            ->first();

        $comments = $article->comments()
            ->where('status', 'approved')
            ->with('user:id,name,avatar,signature,mood_emoji,mood_text')
            ->orderByDesc('created_at')
            ->get();

        $categories = Category::enabled()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->get();

        $hotArticles = Article::where('status', 'published')
            ->orderByDesc('click_num')
            ->take(10)
            ->get(['id', 'title']);

        $seo = [
            'title' => $article->seo_title ?: $article->title,
            'keywords' => $article->seo_keywords ?: \App\Models\Setting::seoKeywords(),
            'description' => $article->seo_description ?: (mb_substr(strip_tags($article->content), 0, 150) ?: \App\Models\Setting::seoDescription()),
        ];

        return view('front.articles.show', compact(
            'article', 'prevArticle', 'nextArticle', 'comments',
            'categories', 'hotArticles', 'seo'
        ));
    }
}
