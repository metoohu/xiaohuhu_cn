<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForbiddenWordViolation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 违禁词违规记录：列表、详情、导出、标记已处理（不可删除）。
 */
class ForbiddenWordViolationController extends Controller
{
    /** 内容类型中文标签 */
    private const CONTENT_TYPE_LABELS = [
        'article' => '运营文章',
        'user_article' => '用户社区稿',
        'comment' => '评论',
        'user_profile' => '会员资料',
        'import_row' => '批量导入行',
    ];

    /** 处理状态中文标签 */
    private const STATUS_LABELS = [
        'pending' => '待处理',
        'rejected' => '已驳回',
        'replaced' => '已自动替换',
        'resolved' => '已处理',
    ];

    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', config('admin.per_page', 10));
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : config('admin.per_page', 10);

        $violations = $this->filteredQuery($request)
            ->with('handler')
            ->latest('checked_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.forbidden-word-violations.index', [
            'violations' => $violations,
            'statusLabels' => self::STATUS_LABELS,
            'contentTypeLabels' => self::CONTENT_TYPE_LABELS,
        ]);
    }

    public function show(ForbiddenWordViolation $forbiddenWordViolation): View
    {
        $forbiddenWordViolation->load('handler');

        return view('admin.forbidden-word-violations.show', [
            'violation' => $forbiddenWordViolation,
            'statusLabels' => self::STATUS_LABELS,
            'contentTypeLabels' => self::CONTENT_TYPE_LABELS,
        ]);
    }

    /**
     * 标记为已处理并记录处理人。
     */
    public function resolve(ForbiddenWordViolation $forbiddenWordViolation): RedirectResponse
    {
        $forbiddenWordViolation->update([
            'status' => 'resolved',
            'handler_admin_id' => Auth::guard('admin')->id(),
        ]);

        return redirect()
            ->route('admin.forbidden-word-violations.show', $forbiddenWordViolation)
            ->with('success', '已标记为已处理');
    }

    /**
     * 按当前筛选条件导出 CSV。
     */
    public function export(Request $request): StreamedResponse
    {
        $rows = $this->filteredQuery($request)
            ->latest('checked_at')
            ->get();

        $filename = 'forbidden_word_violations_'.date('Y-m-d_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $statusLabels = self::STATUS_LABELS;
        $contentTypeLabels = self::CONTENT_TYPE_LABELS;

        $callback = function () use ($rows, $statusLabels, $contentTypeLabels) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, [
                'ID', '内容类型', '内容ID', '标题快照', '字段', '命中词', '分类',
                '动作', '状态', '检测时间', '处理人ID',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id,
                    $contentTypeLabels[$row->content_type] ?? $row->content_type,
                    $row->content_id,
                    $row->content_title_snapshot,
                    $row->field,
                    $row->matched_word,
                    $row->category_slug,
                    $row->action,
                    $statusLabels[$row->status] ?? $row->status,
                    $row->checked_at?->format('Y-m-d H:i:s'),
                    $row->handler_admin_id,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * 列表与导出共用的筛选查询。
     */
    private function filteredQuery(Request $request)
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $categorySlug = $request->input('category_slug');
        $status = $request->input('status');
        $checkedFrom = $request->input('checked_from');
        $checkedTo = $request->input('checked_to');

        return ForbiddenWordViolation::query()
            ->when($categorySlug, fn ($q) => $q->where('category_slug', $categorySlug))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($checkedFrom, fn ($q) => $q->whereDate('checked_at', '>=', $checkedFrom))
            ->when($checkedTo, fn ($q) => $q->whereDate('checked_at', '<=', $checkedTo))
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($inner) use ($keyword) {
                    $inner->where('content_title_snapshot', 'like', "%{$keyword}%")
                        ->orWhere('matched_word', 'like', "%{$keyword}%");
                });
            });
    }
}
