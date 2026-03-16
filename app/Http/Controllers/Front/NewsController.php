<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $news = News::published()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(config('front.article_per_page', 15));

        $categories = Category::enabled()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->get();

        $seo = [
            'title' => '新闻资讯 - ' . (\App\Models\Setting::adminName() ?: '心灵归宿'),
            'keywords' => \App\Models\Setting::seoKeywords(),
            'description' => \App\Models\Setting::seoDescription(),
        ];

        return view('front.news.index', compact('news', 'categories', 'seo'));
    }

    public function show(News $news): View
    {
        if ($news->status !== News::STATUS_PUBLISHED) {
            abort(404);
        }

        $news->increment('click_num');

        $prevNews = News::published()
            ->where('id', '<', $news->id)
            ->orderByDesc('id')
            ->first();

        $nextNews = News::published()
            ->where('id', '>', $news->id)
            ->orderBy('id')
            ->first();

        $categories = Category::enabled()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->get();

        $hotNews = News::published()
            ->orderByDesc('click_num')
            ->take(10)
            ->get(['id', 'title']);

        $seo = [
            'title' => $news->title . ' - 新闻资讯',
            'keywords' => \App\Models\Setting::seoKeywords(),
            'description' => $news->summary ?: mb_substr(strip_tags($news->content), 0, 150) ?: \App\Models\Setting::seoDescription(),
        ];

        return view('front.news.show', compact(
            'news', 'prevNews', 'nextNews', 'categories', 'hotNews', 'seo'
        ));
    }
}
