<?php

use App\Http\Controllers\CrawlerController;
use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

Artisan::command('banner:process {path : 圖片路徑，如 uploads/pugongying.jpeg}', function () {
    $path = $this->argument('path');
    $path = ltrim($path, '/');
    if (! str_starts_with($path, 'uploads/')) {
        $path = 'uploads/' . ltrim(str_replace('uploads/', '', $path), '/');
    }
    $storagePath = storage_path('app/public/' . $path);
    $publicPath = public_path($path);
    if (file_exists($storagePath)) {
        $fullPath = $storagePath;
    } elseif (file_exists($publicPath)) {
        $dir = dirname($storagePath);
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        File::copy($publicPath, $storagePath);
        $this->line("已複製至 storage/app/public/{$path}");
        $fullPath = $storagePath;
    } else {
        $this->error("檔案不存在，請確認路徑：storage/app/public/{$path} 或 public/{$path}");
        return 1;
    }
    $banners = Setting::get('banner_images');
    $list = $banners ? json_decode($banners, true) : [];
    $list = is_array($list) ? $list : [];
    $paths = array_values(array_unique(array_filter(array_merge(
        [$path],
        array_map(fn ($i) => $i['path'] ?? $i['url'] ?? null, $list)
    ))));
    $list = array_slice(array_map(fn ($p) => ['path' => $p, 'url' => $p], $paths), 0, 5);
    Setting::set('banner_images', json_encode($list, JSON_UNESCAPED_UNICODE), '首頁 Banner 圖片');
    $this->info("已將 {$path} 設為首頁 Banner");
    return 0;
})->purpose('將指定圖片設為首頁 Banner');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('crawl:cninfo', function () {
    set_time_limit(0);
    $this->info('開始巨潮採集…');
    $controller = app(CrawlerController::class);
    [$results, $errors] = $controller->runCrawlCninfo();
    $this->info('採集條數：' . count($results));
    if (! empty($errors)) {
        $this->warn('錯誤數：' . count($errors));
        foreach (array_slice($errors, 0, 5) as $err) {
            $this->line('  - ' . json_encode($err, JSON_UNESCAPED_UNICODE));
        }
        if (count($errors) > 5) {
            $this->line('  … 共 ' . count($errors) . ' 筆');
        }
    } else {
        $this->info('無錯誤');
    }
})->purpose('巨潮資訊採集（命令行執行，不佔用 Web 超時）');
