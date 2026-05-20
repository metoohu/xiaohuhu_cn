<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ArticleImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Services\Article\ArticleImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * 运营文章 Excel 批量导入。
 */
class ArticleImportController extends Controller
{
    public function __construct(
        private readonly ArticleImportService $articleImportService,
    ) {
    }

    public function create(): View
    {
        return view('admin.articles.import');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => '请选择 Excel 文件',
            'file.mimes' => '仅支持 xlsx、xls、csv 格式',
        ]);

        $report = $this->articleImportService->import($request->file('file'));

        $message = "导入完成：成功 {$report->success} 条";
        if ($report->failed !== []) {
            $message .= '，失败 '.count($report->failed).' 条';
        }

        return redirect()
            ->route('admin.articles.import')
            ->with('success', $message)
            ->with('import_report', $report->toArray());
    }

    /**
     * 下载导入模板。
     */
    public function template(): BinaryFileResponse
    {
        return Excel::download(
            new ArticleImportTemplateExport,
            'articles-import-template.xlsx',
        );
    }
}
