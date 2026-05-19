<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminOperationLog;
use App\Models\Category;
use App\Models\UserArticle;
use App\Services\UserArticle\UserArticleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * 后台：用户社区稿审核（独立菜单，不写 articles）。
 */
class UserArticleController extends Controller
{
    public function __construct(
        protected UserArticleService $userArticleService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->input('status', UserArticle::STATUS_PENDING_REVIEW);
        $keyword = $request->input('keyword');

        $articles = UserArticle::query()
            ->with(['user', 'category', 'tags'])
            ->when($status !== '' && $status !== null && $status !== 'all', fn ($q) => $q->where('status', (string) $status))
            ->when($keyword, fn ($q) => $q->where('title', 'like', '%'.$keyword.'%'))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate((int) $request->input('per_page', config('admin.per_page', 10)))
            ->withQueryString();

        $categories = Category::orderBy('sort')->orderBy('id')->get(['id', 'name']);

        return view('admin.user-articles.index', compact('articles', 'categories'));
    }

    public function show(UserArticle $userArticle): View
    {
        $userArticle->load(['user', 'category', 'tags', 'reviewer']);

        return view('admin.user-articles.show', compact('userArticle'));
    }

    public function approve(UserArticle $userArticle): RedirectResponse
    {
        if ($userArticle->status !== UserArticle::STATUS_PENDING_REVIEW) {
            return back()->with('error', '仅待审核稿件可通过');
        }

        try {
            $this->userArticleService->approve($userArticle, (int) Auth::guard('admin')->id());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        AdminOperationLog::log('用户投稿审核通过: '.$userArticle->title, '用户投稿');

        return back()->with('success', '已通过并发布（或已更新线上正文）');
    }

    public function reject(Request $request, UserArticle $userArticle): RedirectResponse
    {
        if ($userArticle->status !== UserArticle::STATUS_PENDING_REVIEW) {
            return back()->with('error', '仅待审核稿件可驳回');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        try {
            $this->userArticleService->reject($userArticle, (int) Auth::guard('admin')->id(), (string) $request->input('rejection_reason'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        AdminOperationLog::log('用户投稿驳回: '.$userArticle->title, '用户投稿');

        return back()->with('success', '已驳回');
    }

    public function batchApprove(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:user_articles,id',
        ]);

        $n = 0;
        foreach (UserArticle::whereIn('id', $request->ids)->get() as $ua) {
            if ($ua->status !== UserArticle::STATUS_PENDING_REVIEW) {
                continue;
            }
            try {
                $this->userArticleService->approve($ua, (int) Auth::guard('admin')->id());
                $n++;
            } catch (\Illuminate\Validation\ValidationException) {
                continue;
            }
        }

        AdminOperationLog::log("用户投稿批量通过 {$n} 条", '用户投稿');

        return back()->with('success', "已处理 {$n} 条");
    }

    public function batchReject(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:user_articles,id',
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $reason = (string) $request->input('rejection_reason');
        $n = 0;
        foreach (UserArticle::whereIn('id', $request->ids)->get() as $ua) {
            if ($ua->status !== UserArticle::STATUS_PENDING_REVIEW) {
                continue;
            }
            try {
                $this->userArticleService->reject($ua, (int) Auth::guard('admin')->id(), $reason);
                $n++;
            } catch (\Illuminate\Validation\ValidationException) {
                continue;
            }
        }

        AdminOperationLog::log("用户投稿批量驳回 {$n} 条", '用户投稿');

        return back()->with('success', "已驳回 {$n} 条");
    }
}
