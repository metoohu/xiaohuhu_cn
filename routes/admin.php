<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use Illuminate\Support\Facades\Route;

// 后台登录（无需鉴权）
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('forgot-password', [LoginController::class, 'showForgotPasswordForm'])->name('forgot-password');
Route::post('forgot-password', [LoginController::class, 'sendResetLink']);
Route::get('reset-password/{token}', [LoginController::class, 'showResetPasswordForm'])->name('reset-password');
Route::post('reset-password', [LoginController::class, 'resetPassword'])->name('reset-password.store');

// 验证码
Route::get('captcha', [LoginController::class, 'captcha'])->name('captcha');

// 后台鉴权组
Route::middleware(['admin.auth'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // 仪表盘
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 个人中心
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('update');
        Route::put('password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('password');
        Route::get('logs', [\App\Http\Controllers\Admin\ProfileController::class, 'loginLogs'])->name('logs');
    });

    // 前台注册会员（禁言、资料查看）
    Route::get('members', [\App\Http\Controllers\Admin\MemberController::class, 'index'])->name('members.index');
    Route::get('members/{user}', [\App\Http\Controllers\Admin\MemberController::class, 'show'])->name('members.show');
    Route::post('members/{user}/mute', [\App\Http\Controllers\Admin\MemberController::class, 'mute'])->name('members.mute');
    Route::post('members/{user}/unmute', [\App\Http\Controllers\Admin\MemberController::class, 'unmute'])->name('members.unmute');

    // 用户管理（后台账号）
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::post('users/batch', [\App\Http\Controllers\Admin\UserController::class, 'batchAction'])->name('users.batch');
    Route::get('users/export', [\App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');

    // 角色管理
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);

    // 分类管理（使用 id 綁定，避免 slug 為空時報錯）
    Route::post('categories/batch', [\App\Http\Controllers\Admin\CategoryController::class, 'batchAction'])->name('categories.batch');
    Route::get('categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/create', [\App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('categories.create');
    Route::post('categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::get('categories/{category:id}', [\App\Http\Controllers\Admin\CategoryController::class, 'show'])->name('categories.show');
    Route::get('categories/{category:id}/edit', [\App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('categories/{category:id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category:id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

    // 文章管理
    Route::post('articles/batch', [\App\Http\Controllers\Admin\ArticleController::class, 'batchAction'])->name('articles.batch');
    Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);
    Route::post('articles/upload-image', [\App\Http\Controllers\Admin\ArticleController::class, 'uploadImage'])->name('articles.upload-image');
    Route::post('articles/{article}/approve', [\App\Http\Controllers\Admin\ArticleController::class, 'approve'])->name('articles.approve');
    Route::post('articles/{article}/reject', [\App\Http\Controllers\Admin\ArticleController::class, 'reject'])->name('articles.reject');

    // 评论管理
    Route::resource('comments', \App\Http\Controllers\Admin\CommentController::class)->except(['create', 'store']);
    Route::post('comments/{comment}/approve', [\App\Http\Controllers\Admin\CommentController::class, 'approve'])->name('comments.approve');
    Route::post('comments/{comment}/reject', [\App\Http\Controllers\Admin\CommentController::class, 'reject'])->name('comments.reject');

    // 系统设置
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // 日志管理
    Route::get('logs', [\App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index');
    Route::get('logs/operation', [\App\Http\Controllers\Admin\LogController::class, 'operation'])->name('logs.operation');
    Route::get('logs/error', [\App\Http\Controllers\Admin\LogController::class, 'error'])->name('logs.error');

    // 备份管理
    Route::get('backups', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backups.index');
    Route::post('backups', [\App\Http\Controllers\Admin\BackupController::class, 'store'])->name('backups.store');
    Route::delete('backups/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('backups.destroy');
    Route::get('backups/{filename}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');
});
