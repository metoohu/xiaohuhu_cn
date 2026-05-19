<?php

namespace App\Console\Commands;

use App\Jobs\GenerateEmotionalArticleJob;
use App\Models\ArticleGenerationRun;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;

/**
 * 为每个「启用且无子分类」的叶子类目投递一篇豆包情感文生成任务（按应用时区自然日幂等）。
 */
class ScheduleEmotionalArticlesCommand extends Command
{
    protected $signature = 'articles:schedule-emotional-daily';

    protected $description = '按叶子类目投递每日 AI 情感文生成任务（豆包，入库为待审核）';

    public function handle(): int
    {
        if (! config('doubao.enabled')) {
            $this->warn('已跳过：DOUBAO_ENABLED 未开启或未配置豆包。');

            return self::SUCCESS;
        }

        $runDate = now()->toDateString();
        $leaves = Category::query()->enabledLeaves()->orderBy('id')->get();
        if ($leaves->isEmpty()) {
            $this->info('无启用的叶子类目，未投递任务。');

            return self::SUCCESS;
        }

        $dispatched = 0;
        foreach ($leaves as $category) {
            if (! $category->isLeaf()) {
                continue;
            }

            $existingSuccess = ArticleGenerationRun::query()
                ->where('category_id', $category->id)
                ->whereDate('run_date', $runDate)
                ->where('status', ArticleGenerationRun::STATUS_SUCCESS)
                ->exists();
            if ($existingSuccess) {
                continue;
            }

            // 使用 whereDate + 显式创建，避免 SQLite 下 firstOrCreate 与 date 列比较不一致导致重复 INSERT
            $run = ArticleGenerationRun::query()
                ->where('category_id', $category->id)
                ->whereDate('run_date', $runDate)
                ->first();
            if (! $run) {
                try {
                    $run = ArticleGenerationRun::query()->create([
                        'category_id' => $category->id,
                        'run_date' => $runDate,
                        'status' => ArticleGenerationRun::STATUS_PENDING,
                    ]);
                } catch (UniqueConstraintViolationException) {
                    $run = ArticleGenerationRun::query()
                        ->where('category_id', $category->id)
                        ->whereDate('run_date', $runDate)
                        ->firstOrFail();
                }
            }

            // 短时间内的 processing 视为仍在执行，避免重复投递
            if ($run->status === ArticleGenerationRun::STATUS_PROCESSING) {
                if ($run->updated_at && $run->updated_at->gt(now()->subMinutes(15))) {
                    continue;
                }
                // 超时未结束：允许重新投递
                $run->forceFill(['status' => ArticleGenerationRun::STATUS_PENDING, 'error_message' => null])->save();
            }

            if ($run->status === ArticleGenerationRun::STATUS_SUCCESS) {
                continue;
            }

            if ($run->status === ArticleGenerationRun::STATUS_FAILED) {
                $run->forceFill(['status' => ArticleGenerationRun::STATUS_PENDING, 'error_message' => null])->save();
            }

            GenerateEmotionalArticleJob::dispatch($run->id);
            $dispatched++;
        }

        Log::info('articles.schedule_emotional', ['date' => $runDate, 'dispatched' => $dispatched]);
        $this->info("已投递 {$dispatched} 个生成任务（日期 {$runDate}）。");

        return self::SUCCESS;
    }
}
