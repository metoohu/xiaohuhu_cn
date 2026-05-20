<?php

namespace App\Services\Article\Dto;

/**
 * 文章 Excel 批量导入结果汇总。
 */
readonly class ArticleImportReport
{
    /**
     * @param  array<int, array{row: int, messages: array<int, string>}>  $failed
     */
    public function __construct(
        public int $success,
        public array $failed,
    ) {
    }

    /**
     * @return array{success: int, failed: array<int, array{row: int, messages: array<int, string>}>}
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'failed' => $this->failed,
        ];
    }
}
