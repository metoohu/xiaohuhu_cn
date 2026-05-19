<?php

use App\Http\Controllers\CrawlerController;
use App\Http\Controllers\Front\AboutController;
use App\Http\Controllers\Front\AuthController;
use App\Http\Controllers\Front\CompanyInfoController;
use App\Http\Controllers\Front\ArticleController;
use App\Http\Controllers\Front\CategoryController;
use App\Http\Controllers\Front\CommentController;
use App\Http\Controllers\Front\CommunityUserArticleController;
use App\Http\Controllers\Front\MyUserArticleController;
use App\Http\Controllers\Front\UserProfileController;
use App\Http\Controllers\Front\UserStickerController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\NewsController;
use App\Http\Controllers\Front\SearchController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// ========== 前台路由 ==========

// PWA / 移动端「添加到主屏幕」清单（动态站点名）
Route::get('manifest.webmanifest', function () {
    $name = \App\Models\Setting::adminName() ?: '小糊涂人生馆';

    return response()->json([
        'name' => $name,
        'short_name' => (string) Str::limit($name, 12, ''),
        'description' => \App\Models\Setting::seoDescription() ?: '在喧嚣中寻一方宁静，用文字温暖你我',
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => '#f9f7f5',
        'theme_color' => '#6b8e82',
        'lang' => 'zh-CN',
    ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ->header('Content-Type', 'application/manifest+json; charset=utf-8');
})->name('front.manifest');

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

// 用户社区稿（已发布，公共读）
Route::get('community/{userArticle}', [CommunityUserArticleController::class, 'show'])->name('front.community.show');

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

    // 我的社区稿（用户投稿）
    Route::get('articles', [MyUserArticleController::class, 'index'])->name('articles');
    // 须在 articles/{userArticle} 之前注册，避免被动态段吞掉
    Route::post('articles/upload-image', [MyUserArticleController::class, 'uploadImage'])->name('articles.upload-image');
    Route::get('articles/create', [MyUserArticleController::class, 'create'])->name('articles.create');
    Route::post('articles', [MyUserArticleController::class, 'store'])->name('articles.store');
    Route::get('articles/{userArticle}/edit', [MyUserArticleController::class, 'edit'])->name('articles.edit');
    Route::put('articles/{userArticle}', [MyUserArticleController::class, 'update'])->name('articles.update');
    Route::delete('articles/{userArticle}', [MyUserArticleController::class, 'destroy'])->name('articles.destroy');
    Route::post('articles/{userArticle}/submit', [MyUserArticleController::class, 'submit'])->name('articles.submit');
    Route::post('articles/{userArticle}/withdraw', [MyUserArticleController::class, 'withdraw'])->name('articles.withdraw');
});

// ========== 公司信息（原有功能） ==========

Route::get('company-info', [CompanyInfoController::class, 'index'])->name('company-info');

// 采集巨潮资讯
Route::get('/crawl-cninfo', [CrawlerController::class, 'crawlCninfo']);

// 404 兜底
Route::fallback([HomeController::class, 'notFound']);
