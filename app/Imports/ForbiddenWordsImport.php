<?php

namespace App\Imports;

use App\Models\ForbiddenWord;
use App\Models\ForbiddenWordCategory;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * 违禁词 Excel 逐行导入（列：category_slug, word, match_type, replacement, is_enabled, remark）。
 */
class ForbiddenWordsImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    /** @var array<int, string> */
    private array $categorySlugCache = [];

    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?ForbiddenWord
    {
        $slug = $this->cell($row, 'category_slug');
        $categoryId = $this->resolveCategoryId($slug);

        if ($categoryId === null) {
            throw ValidationException::withMessages([
                'category_slug' => '分类 slug 不存在：'.(string) $slug,
            ]);
        }

        $category = ForbiddenWordCategory::query()->find($categoryId);
        if ($category !== null
            && $category->level === ForbiddenWordCategory::LEVEL_TONE
            && blank($this->cell($row, 'replacement'))) {
            throw ValidationException::withMessages([
                'replacement' => '调性违规类词条必须填写 replacement',
            ]);
        }

        return new ForbiddenWord([
            'category_id' => $categoryId,
            'word' => (string) $this->cell($row, 'word'),
            'match_type' => $this->cell($row, 'match_type') ?: 'exact',
            'replacement' => $this->nullableCell($row, 'replacement'),
            'is_enabled' => $this->parseEnabled($row),
            'remark' => $this->nullableCell($row, 'remark'),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rules(): array
    {
        return [
            '*.category_slug' => ['required', 'string'],
            '*.word' => ['required', 'string', 'max:100'],
            '*.match_type' => ['nullable', Rule::in(['exact', 'fuzzy'])],
            '*.replacement' => ['nullable', 'string', 'max:100'],
            '*.is_enabled' => ['nullable'],
            '*.remark' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function cell(array $row, string $key): mixed
    {
        return $row[$key] ?? $row[str_replace('_', ' ', $key)] ?? null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function nullableCell(array $row, string $key): ?string
    {
        $value = $this->cell($row, $key);

        return $value === null || $value === '' ? null : (string) $value;
    }

    private function resolveCategoryId(?string $slug): ?int
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        if (! array_key_exists($slug, $this->categorySlugCache)) {
            $category = ForbiddenWordCategory::query()->where('slug', $slug)->first();
            $this->categorySlugCache[$slug] = $category?->id ? (string) $category->id : '';
        }

        $cached = $this->categorySlugCache[$slug];

        return $cached === '' ? null : (int) $cached;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function parseEnabled(array $row): bool
    {
        $value = $this->cell($row, 'is_enabled');

        if ($value === null || $value === '') {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'y', '是', '启用', 'on'], true);
    }

}
