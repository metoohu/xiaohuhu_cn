<?php

namespace App\Exports;

use App\Models\ForbiddenWord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * 导出全部违禁词条（明文 word，含 category_slug）。
 */
class ForbiddenWordsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return ForbiddenWord::query()
            ->with('category')
            ->orderBy('category_id')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'category_slug',
            'word',
            'match_type',
            'replacement',
            'is_enabled',
            'remark',
        ];
    }

    /**
     * @param  ForbiddenWord  $word
     * @return array<int, mixed>
     */
    public function map($word): array
    {
        return [
            $word->category?->slug ?? '',
            $word->word,
            $word->match_type,
            $word->replacement,
            $word->is_enabled ? 1 : 0,
            $word->remark,
        ];
    }
}
