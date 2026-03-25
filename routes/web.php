<?php

use App\Http\Controllers\CrawlerController;
use App\Http\Controllers\Front\AboutController;
use App\Http\Controllers\Front\AuthController;
use App\Http\Controllers\Front\CompanyInfoController;
use App\Http\Controllers\Front\ArticleController;
use App\Http\Controllers\Front\CategoryController;
use App\Http\Controllers\Front\CommentController;
use App\Http\Controllers\Front\UserProfileController;
use App\Http\Controllers\Front\UserStickerController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\NewsController;
use App\Http\Controllers\Front\SearchController;
use Illuminate\Support\Facades\Route;

// ========== 前台路由 ==========

// 首页
Route::get('/', [HomeController::class, 'index'])->name('front.home');

// 会员注册、登录、登出
Route::get('register', [AuthController::class, 'showRegisterForm'])->name('front.register');
Route::post('register', [AuthController::class, 'register']);
Route::get('login', [AuthController::class, 'showLoginForm'])->name('front.login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('front.logout');
Route::get('auth/captcha', [AuthController::class, 'captcha'])->name('front.captcha');

// 文章
Route::get('articles', [ArticleController::class, 'index'])->name('front.articles.index');
Route::get('articles/{article}', [ArticleController::class, 'show'])->name('front.articles.show');

// 分类（前台使用 slug 解析）
Route::get('categories', [CategoryController::class, 'index'])->name('front.categories.index');
Route::get('categories/{category:slug}', [CategoryController::class, 'show'])->name('front.categories.show');

// 搜索
Route::get('search', [SearchController::class, 'index'])->name('front.search');

// 关于我们
Route::get('about', [AboutController::class, 'index'])->name('front.about');

// 留言板已移除，重定向至首页
Route::get('message', fn () => redirect('/'))->name('front.message');

// 新闻资讯
Route::get('news', [NewsController::class, 'index'])->name('front.news.index');
Route::get('news/{news}', [NewsController::class, 'show'])->name('front.news.show');

// 评论提交（异步）
Route::post('comments', [CommentController::class, 'store'])
    ->middleware('front.active')
    ->name('front.comments.store');

// 登录用户：表情包管理（评论区可选用）
Route::middleware(['auth', 'front.active'])->prefix('my')->name('front.my.')->group(function () {
    Route::get('profile', [UserProfileController::class, 'edit'])->name('profile');
    Route::put('profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::get('stickers', [UserStickerController::class, 'index'])->name('stickers');
    Route::get('stickers/json', [UserStickerController::class, 'json'])->name('stickers.json');
    Route::post('stickers', [UserStickerController::class, 'store'])->name('stickers.store');
    Route::delete('stickers/{userSticker}', [UserStickerController::class, 'destroy'])->name('stickers.destroy');
});

// ========== 公司信息（原有功能） ==========

Route::get('company-info', [CompanyInfoController::class, 'index'])->name('company-info');

// 采集巨潮资讯
Route::get('/crawl-cninfo', [CrawlerController::class, 'crawlCninfo']);

// 404 兜底
Route::fallback([HomeController::class, 'notFound']);
