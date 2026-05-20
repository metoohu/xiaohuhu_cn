<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * 文章批量导入 Excel 空模板（含示例行）。
 */
class ArticleImportTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'title',
            'content',
            'excerpt',
            'seo_title',
            'seo_keywords',
            'seo_description',
            'category_id',
        ];
    }

    public function array(): array
    {
        return [
            [
                '示例标题',
                '示例正文内容',
                '示例摘要',
                'SEO 标题',
                '关键词1,关键词2',
                'SEO 描述',
                1,
            ],
        ];
    }
}
