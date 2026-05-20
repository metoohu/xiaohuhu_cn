<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * 文章批量导入：仅用于 Excel::toArray 解析表头行。
 */
class ArticleBulkRowsImport implements WithHeadingRow
{
}
