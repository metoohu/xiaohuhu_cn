<?php

use App\Http\Controllers\CrawlerController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

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
