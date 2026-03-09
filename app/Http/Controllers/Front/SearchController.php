<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->get('q', $request->get('keyword', ''));

        $articles = Article::where('status', 'published')
            ->when($keyword, function ($q) use ($keyword) {
                $k = '%' . $keyword . '%';
                $q->where(function ($query) use ($k) {
                    $query->where('title', 'like', $k)
                        ->orWhere('content', 'like', $k);
                });
            })
            ->with('category')
            ->orderByDesc('created_at')
            ->paginate(config('front.article_per_page', 15));

        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->get();

        $seo = [
            'title' => ($keyword ? "搜索：{$keyword} - " : '搜索 - ') . (\App\Models\Setting::adminName() ?: '内容展示'),
            'keywords' => \App\Models\Setting::seoKeywords(),
            'description' => \App\Models\Setting::seoDescription(),
        ];

        return view('front.search.index', compact('articles', 'categories', 'keyword', 'seo'));
    }
}
