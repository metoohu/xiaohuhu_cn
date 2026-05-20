<?php

namespace App\Services\Article;

use App\Exceptions\ForbiddenContentException;
use App\Models\Article;
use App\Models\Category;
use App\Services\Article\Dto\ArticleImportReport;
use App\Services\ForbiddenWord\ForbiddenContentService;
use App\Imports\ArticleBulkRowsImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

/**
 * 运营文章 Excel 逐行导入：每行违禁词校验，违规行不入库。
 */
class ArticleImportService
{
    public function __construct(
        private readonly ForbiddenContentService $forbiddenContentService,
    ) {
    }

    /**
     * 解析 Excel 并逐行校验、入库。
     */
    public function import(UploadedFile $file, ?int $adminUserId = null): ArticleImportReport
    {
        $adminUserId ??= (int) Auth::guard('admin')->id();

        $rows = Excel::toArray(new ArticleBulkRowsImport, $file)[0] ?? [];

        $success = 0;
        $failed = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            try {
                $this->importRow($row, $adminUserId);
                $success++;
            } catch (ForbiddenContentException $e) {
                $failed[] = [
                    'row' => $rowNumber,
                    'messages' => $e->result->messages,
                ];
            } catch (ValidationException $e) {
                $messages = collect($e->errors())->flatten()->values()->all();
                $failed[] = [
                    'row' => $rowNumber,
                    'messages' => $messages !== [] ? $messages : ['行数据校验失败'],
                ];
            }
        }

        return new ArticleImportReport($success, $failed);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function importRow(array $row, int $adminUserId): void
    {
        $title = trim((string) ($row['title'] ?? ''));
        $content = trim((string) ($row['content'] ?? ''));
        $categoryId = (int) ($row['category_id'] ?? 0);

        if ($title === '' || $content === '') {
            throw ValidationException::withMessages([
                'title' => '标题与正文为必填',
            ]);
        }

        if ($categoryId <= 0 || ! Category::query()->whereKey($categoryId)->exists()) {
            throw ValidationException::withMessages([
                'category_id' => '分类 ID 无效',
            ]);
        }

        $fields = [
            'title' => $title,
            'body' => $content,
            'excerpt' => trim((string) ($row['excerpt'] ?? '')),
            'seo_title' => trim((string) ($row['seo_title'] ?? '')),
            'seo_keywords' => trim((string) ($row['seo_keywords'] ?? '')),
            'seo_description' => trim((string) ($row['seo_description'] ?? '')),
        ];

        $checked = $this->forbiddenContentService->assertOrReplace(
            $fields,
            'import_row',
            $title,
            'import_row',
            null,
        );

        $merged = $checked['fields'];

        Article::query()->create([
            'title' => $merged['title'] ?? $title,
            'content' => $merged['body'] ?? $content,
            'category_id' => $categoryId,
            'admin_user_id' => $adminUserId,
            'status' => Article::STATUS_DRAFT,
            'seo_title' => $merged['seo_title'] ?? null,
            'seo_keywords' => $merged['seo_keywords'] ?? null,
            'seo_description' => $merged['seo_description'] ?? null,
            'click_num' => 0,
            'is_recommend' => false,
            'sort' => 0,
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
