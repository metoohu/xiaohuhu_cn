<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CompanyInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class CompanyInfoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('export') && $request->get('export') === '1') {
            return $this->export();
        }

        $companies = CompanyInfo::query()
            ->orderByDesc('id')
            ->paginate(15);

        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->get();

        $seo = [
            'title' => '公司信息 - ' . (\App\Models\Setting::adminName() ?: '內容展示'),
            'keywords' => \App\Models\Setting::seoKeywords(),
            'description' => \App\Models\Setting::seoDescription(),
        ];

        return view('front.company-info.index', compact('companies', 'categories', 'seo'));
    }

    protected function export()
    {
        $rows = CompanyInfo::query()
            ->orderByDesc('id')
            ->get(['abbreviation', 'contact_number', 'nature_business']);

        $filename = 'company_info_' . date('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['公司简称', '联系电话', '经营范围']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->abbreviation ?? '',
                    $row->contact_number ?? '',
                    $row->nature_business ?? '',
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
