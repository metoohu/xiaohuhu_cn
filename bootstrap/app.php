<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 前台 auth 中间件未登录时跳转前台登录页（非默认 route('login')）
        $middleware->redirectGuestsTo(fn () => route('front.login'));
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'front.active' => \App\Http\Middleware\EnsureFrontUserNotDisabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '页面未找到'], 404);
            }
            return app(\App\Http\Controllers\Front\HomeController::class)->notFound();
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        // 每日为每个启用叶子类目投递一篇豆包情感文（队列执行，见 articles:schedule-emotional-daily）
        $schedule->command('articles:schedule-emotional-daily')->dailyAt('02:00');
    })
    ->create();
