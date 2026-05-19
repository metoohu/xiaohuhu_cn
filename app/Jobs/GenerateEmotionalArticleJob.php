<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\ArticleGenerationRun;
use App\Services\EmotionalArticle\DoubaoEmotionalArticleClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 按 article_generation_runs 记录调用豆包生成情感文并写入待审核文章。
 */
class GenerateEmotionalArticleJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 180;

    public int $uniqueFor = 7200;

    public function __construct(
        public int $articleGenerationRunId,
    ) {}

    public function uniqueId(): string
    {
        return 'emotional-article-run-'.$this->articleGenerationRunId;
    }

    public function handle(DoubaoEmotionalArticleClient $client): void
    {
        $run = ArticleGenerationRun::query()->find($this->articleGenerationRunId);
        if (! $run) {
            return;
        }

        $mayProceed = DB::transaction(function () use ($run) {
            $r = ArticleGenerationRun::query()->whereKey($run->id)->lockForUpdate()->first();
            if (! $r) {
                return false;
            }
            if ($r->status === ArticleGenerationRun::STATUS_SUCCESS) {
                return false;
            }
            if ($r->status === ArticleGenerationRun::STATUS_PROCESSING) {
                return false;
            }
            $r->forceFill([
                'status' => ArticleGenerationRun::STATUS_PROCESSING,
                'error_message' => null,
            ])->save();

            return true;
        });

        if ($mayProceed !== true) {
            return;
        }

        $run->refresh();
        $category = $run->category;
        if (! $category) {
            $this->markFailed($run, '分类不存在');

            return;
        }

        try {
            $payload = $client->generate($category);
            $article = Article::query()->create([
                'title' => $payload['title'],
                'content' => $payload['content'],
                'category_id' => $category->id,
                'admin_user_id' => null,
                'status' => Article::STATUS_REVIEW,
                'seo_title' => $payload['seo_title'],
                'seo_keywords' => $payload['seo_keywords'],
                'seo_description' => $payload['seo_description'],
                'click_num' => 0,
                'is_recommend' => false,
                'sort' => 0,
            ]);
            $run->forceFill([
                'status' => ArticleGenerationRun::STATUS_SUCCESS,
                'article_id' => $article->id,
                'error_message' => null,
            ])->save();
        } catch (Throwable $e) {
            Log::error('GenerateEmotionalArticleJob 失败', [
                'run_id' => $run->id,
                'message' => $e->getMessage(),
            ]);
            $this->markFailed($run, mb_substr($e->getMessage(), 0, 2000));
        }
    }

    private function markFailed(ArticleGenerationRun $run, string $message): void
    {
        $run->forceFill([
            'status' => ArticleGenerationRun::STATUS_FAILED,
            'error_message' => $message,
        ])->save();
    }
}
