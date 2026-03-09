<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $cacheKey = 'front_home_data_' . now()->format('YmdHi');
        $data = Cache::remember($cacheKey, config('front.cache_ttl', 30) * 60, function () {
            $banners = Setting::get('banner_images');
            $banners = $banners ? json_decode($banners, true) : [];

            return [
                'banners' => $banners,
                'recommend_articles' => Article::where('status', 'published')
                    ->where('is_recommend', true)
                    ->orderBy('sort')
                    ->orderByDesc('created_at')
                    ->take(6)
                    ->get(),
                'latest_articles' => Article::where('status', 'published')
                    ->with('category')
                    ->orderByDesc('created_at')
                    ->take(config('front.home_article_count', 10))
                    ->get(),
            ];
        });

        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->with(['children' => fn ($q) => $q->where('status', 1)->orderBy('sort')])
            ->get();

        $seo = [
            'title' => Setting::get('site_title', Setting::adminName()),
            'keywords' => Setting::seoKeywords(),
            'description' => Setting::seoDescription(),
        ];

        return view('front.home.index', array_merge($data, [
            'categories' => $categories,
            'seo' => $seo,
        ]));
    }

    public function notFound()
    {
        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->get();

        return response()->view('front.404', [
            'categories' => $categories,
            'seo' => [
                'title' => '页面未找到',
                'keywords' => Setting::seoKeywords(),
                'description' => Setting::seoDescription(),
            ],
        ], 404);
    }
}
