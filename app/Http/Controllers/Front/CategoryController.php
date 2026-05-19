<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use App\Services\Front\FrontHomeFeedMerger;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::enabled()
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
        $category->load(['children' => fn ($q) => $q->where('status', Category::STATUS_ENABLED)->orderBy('sort')]);

        if ($category->status != Category::STATUS_ENABLED) {
            abort(404);
        }

        $categoryIds = $category->children->pluck('id')->push($category->id)->toArray();

        // 双源列表：复用 FrontHomeFeedMerger 的 UNION 分页实现（与首页 merge 排序语义一致）。
        /** @var FrontHomeFeedMerger $feedMerger */
        $feedMerger = app(FrontHomeFeedMerger::class);
        $feeds = $feedMerger->paginateCategoryDualSource($categoryIds);

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
            'title' => $category->name . ' - ' . (Setting::adminName() ?: '内容展示'),
            'keywords' => $category->name . ',' . \App\Models\Setting::seoKeywords(),
            'description' => $category->description ?: \App\Models\Setting::seoDescription(),
        ];

        return view('front.categories.show', compact('category', 'feeds', 'categories', 'hotArticles', 'seo'));
    }
}
