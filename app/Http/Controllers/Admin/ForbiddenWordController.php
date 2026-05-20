<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ForbiddenWordsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportForbiddenWordsRequest;
use App\Http\Requests\Admin\StoreForbiddenWordRequest;
use App\Http\Requests\Admin\UpdateForbiddenWordRequest;
use App\Imports\ForbiddenWordsImport;
use App\Models\ForbiddenWord;
use App\Models\ForbiddenWordCategory;
use App\Services\ForbiddenWord\ForbiddenWordMaintenanceLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * 后台违禁词库 CRUD、批量启用/禁用、Excel 导入导出。
 */
class ForbiddenWordController extends Controller
{
    public function __construct(
        private readonly ForbiddenWordMaintenanceLogger $maintenanceLogger,
    ) {
    }

    /**
     * 词条列表（分类、关键词、启用状态筛选）。
     */
    public function index(Request $request): View
    {
        $query = ForbiddenWord::query()
            ->with('category')
            ->when(
                $request->filled('category_id'),
                fn ($q) => $q->where('category_id', (int) $request->input('category_id'))
            )
            ->when(
                $request->has('is_enabled') && $request->input('is_enabled') !== '',
                fn ($q) => $q->where('is_enabled', (bool) $request->boolean('is_enabled'))
            )
            ->orderByDesc('id');

        if ($request->filled('keyword')) {
            $keyword = (string) $request->input('keyword');
            $matchedIds = ForbiddenWord::query()
                ->with('category')
                ->get()
                ->filter(fn (ForbiddenWord $word) => str_contains($word->word, $keyword))
                ->pluck('id')
                ->all();

            $query->whereIn('id', $matchedIds ?: [0]);
        }

        $words = $query->paginate(config('admin.per_page', 10))->withQueryString();
        $categories = ForbiddenWordCategory::query()->orderBy('sort')->orderBy('id')->get();

        return view('admin.forbidden-words.index', compact('words', 'categories'));
    }

    public function create(): View
    {
        $categories = ForbiddenWordCategory::query()->orderBy('sort')->orderBy('id')->get();

        return view('admin.forbidden-words.create', compact('categories'));
    }

    public function store(StoreForbiddenWordRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_enabled'] = $request->boolean('is_enabled', true);

        $word = ForbiddenWord::query()->create($data);

        $this->maintenanceLogger->log('create', 'forbidden_word', $word->id, [
            'category_id' => $word->category_id,
        ]);

        return redirect()
            ->route('admin.forbidden-words.index')
            ->with('success', '违禁词条已创建');
    }

    public function edit(ForbiddenWord $forbiddenWord): View
    {
        $categories = ForbiddenWordCategory::query()->orderBy('sort')->orderBy('id')->get();

        return view('admin.forbidden-words.edit', [
            'forbiddenWord' => $forbiddenWord,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateForbiddenWordRequest $request, ForbiddenWord $forbiddenWord): RedirectResponse
    {
        $data = $request->validated();
        $data['is_enabled'] = $request->boolean('is_enabled', true);

        $forbiddenWord->update($data);

        $this->maintenanceLogger->log('update', 'forbidden_word', $forbiddenWord->id, [
            'category_id' => $forbiddenWord->category_id,
        ]);

        return redirect()
            ->route('admin.forbidden-words.index')
            ->with('success', '违禁词条已更新');
    }

    public function destroy(ForbiddenWord $forbiddenWord): RedirectResponse
    {
        $id = $forbiddenWord->id;
        $forbiddenWord->delete();

        $this->maintenanceLogger->log('delete', 'forbidden_word', $id, []);

        return redirect()
            ->route('admin.forbidden-words.index')
            ->with('success', '违禁词条已删除');
    }

    /**
     * 批量启用或禁用词条。
     */
    public function batch(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:enable,disable',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:forbidden_words,id',
        ]);

        $ids = $request->input('ids', []);
        $enabled = $request->input('action') === 'enable';

        $count = ForbiddenWord::query()
            ->whereIn('id', $ids)
            ->update(['is_enabled' => $enabled]);

        $this->maintenanceLogger->log(
            $enabled ? 'enable' : 'disable',
            'forbidden_word',
            null,
            ['ids' => $ids, 'count' => $count],
        );

        $label = $enabled ? '启用' : '禁用';

        return back()->with('success', "已批量{$label} {$count} 条词条");
    }

    /**
     * Excel 导入页。
     */
    public function importForm(): View
    {
        return view('admin.forbidden-words.import');
    }

    /**
     * 处理 Excel 导入。
     */
    public function import(ImportForbiddenWordsRequest $request): RedirectResponse
    {
        $beforeCount = ForbiddenWord::query()->count();
        $import = new ForbiddenWordsImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            $this->maintenanceLogger->log('import', 'forbidden_word', null, [
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', '导入失败：'.$e->getMessage());
        }

        $imported = ForbiddenWord::query()->count() - $beforeCount;
        $failures = $import->failures();

        $this->maintenanceLogger->log('import', 'forbidden_word', null, [
            'imported' => $imported,
            'failed_rows' => $failures->count(),
        ]);

        $message = "导入完成：成功 {$imported} 条";
        if ($failures->isNotEmpty()) {
            $lines = $failures->take(10)->map(
                fn ($failure) => '第 '.$failure->row().' 行：'.implode('；', $failure->errors())
            )->implode('；');

            return back()
                ->with('success', $message)
                ->with('warning', '部分行未导入：'.$lines);
        }

        return back()->with('success', $message);
    }

    /**
     * 导出全部词条为 xlsx。
     */
    public function export(): BinaryFileResponse
    {
        $filename = 'forbidden_words_'.date('Y-m-d_His').'.xlsx';

        $this->maintenanceLogger->log('export', 'forbidden_word', null, [
            'filename' => $filename,
        ]);

        return Excel::download(new ForbiddenWordsExport, $filename);
    }
}
