<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminUser;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\CompanyInfo;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $cacheKey = 'admin_dashboard_stats_' . now()->format('YmdHi');
        $stats = Cache::remember($cacheKey, config('admin.cache_time', 5) * 60, function () {
            return [
                'users_count' => AdminUser::count(),
                'articles_count' => Article::count(),
                'categories_count' => Category::count(),
                'comments_count' => Comment::count(),
                'companies_count' => CompanyInfo::count(),
            ];
        });

        $recentArticles = Article::with('category')->latest()->take(5)->get();
        $pendingComments = Comment::where('status', 'pending')->count();

        return view('admin.dashboard.index', compact('stats', 'recentArticles', 'pendingComments'));
    }
}
