<?php

use App\Http\Controllers\CrawlerController;
use App\Http\Controllers\Front\AboutController;
use App\Http\Controllers\Front\CompanyInfoController;
use App\Http\Controllers\Front\ArticleController;
use App\Http\Controllers\Front\CategoryController;
use App\Http\Controllers\Front\CommentController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\NewsController;
use App\Http\Controllers\Front\SearchController;
use Illuminate\Support\Facades\Route;

// ========== 前台路由 ==========

// 首页
Route::get('/', [HomeController::class, 'index'])->name('front.home');

// 文章
Route::get('articles', [ArticleController::class, 'index'])->name('front.articles.index');
Route::get('articles/{article}', [ArticleController::class, 'show'])->name('front.articles.show');

// 分类
Route::get('categories', [CategoryController::class, 'index'])->name('front.categories.index');
Route::get('categories/{category}', [CategoryController::class, 'show'])->name('front.categories.show');

// 搜索
Route::get('search', [SearchController::class, 'index'])->name('front.search');

// 关于我们
Route::get('about', [AboutController::class, 'index'])->name('front.about');

// 新闻资讯
Route::get('news', [NewsController::class, 'index'])->name('front.news.index');
Route::get('news/{news}', [NewsController::class, 'show'])->name('front.news.show');

// 评论提交（异步）
Route::post('comments', [CommentController::class, 'store'])->name('front.comments.store');

// ========== 公司信息（原有功能） ==========

Route::get('company-info', [CompanyInfoController::class, 'index'])->name('company-info');

// 采集巨潮资讯
Route::get('/crawl-cninfo', [CrawlerController::class, 'crawlCninfo']);

// 404 兜底
Route::fallback([HomeController::class, 'notFound']);
