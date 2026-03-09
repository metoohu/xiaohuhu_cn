<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->get();

        $seo = [
            'title' => '文章分类 - ' . (Setting::adminName() ?: '心灵归宿'),
            'keywords' => Setting::seoKeywords(),
            'description' => Setting::seoDescription(),
        ];

        return view('front.categories.index', compact('categories', 'seo'));
    }

    public function show(Category $category): View
    {
        $category->load('children');

        if (isset($category->status) && $category->status != 1) {
            abort(404);
        }

        $categoryIds = $category->children->pluck('id')->push($category->id)->toArray();

        $articles = Article::where('status', 'published')
            ->whereIn('category_id', $categoryIds)
            ->with('category')
            ->orderByDesc('created_at')
            ->paginate(config('front.article_per_page', 15));

        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->get();

        $hotArticles = Article::where('status', 'published')
            ->orderByDesc('click_num')
            ->take(10)
            ->get(['id', 'title']);

        $seo = [
            'title' => $category->name . ' - ' . (Setting::adminName() ?: '内容展示'),
            'keywords' => $category->name . ',' . \App\Models\Setting::seoKeywords(),
            'description' => $category->description ?: \App\Models\Setting::seoDescription(),
        ];

        return view('front.categories.show', compact('category', 'articles', 'categories', 'hotArticles', 'seo'));
    }
}
