<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminOperationLog;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $status = $request->input('status');
        $categoryId = $request->input('category_id');
        $perPage = (int) $request->input('per_page', config('admin.per_page', 10));
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : config('admin.per_page', 10);

        $articles = Article::query()
            ->with(['category', 'adminUser'])
            ->when($keyword, fn ($q) => $q->where('title', 'like', "%{$keyword}%"))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $categories = Category::orderBy('sort')->get();

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('sort')->get();

        return view('admin.articles.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,review',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('title', 'content', 'category_id', 'status');
        $data['admin_user_id'] = Auth::guard('admin')->id();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store(config('admin.upload_path', 'uploads'), 'public');
        }

        $article = Article::create($data);

        AdminOperationLog::log('创建文章: ' . $article->title, '文章管理');

        return redirect()->route('admin.articles.index')->with('success', '文章创建成功');
    }

    public function show(Article $article): View
    {
        $article->load(['category', 'adminUser', 'comments']);

        return view('admin.articles.show', compact('article'));
    }

    public function edit(Article $article): View
    {
        $categories = Category::orderBy('sort')->get();

        return view('admin.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,review,published',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('title', 'content', 'category_id', 'status');

        if ($request->hasFile('cover_image')) {
            if ($article->cover_image) {
                Storage::disk('public')->delete($article->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store(config('admin.upload_path', 'uploads'), 'public');
        }

        $article->update($data);

        AdminOperationLog::log('更新文章: ' . $article->title, '文章管理');

        return redirect()->route('admin.articles.index')->with('success', '文章更新成功');
    }

    public function batchAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'ids' => 'required|array',
            'ids.*' => 'exists:articles,id',
        ]);

        $articles = Article::whereIn('id', $request->ids)
            ->where('status', Article::STATUS_REVIEW)
            ->get();

        $reviewComment = $request->input('review_comment', '');

        foreach ($articles as $article) {
            if ($request->action === 'approve') {
                $article->update([
                    'status' => Article::STATUS_PUBLISHED,
                    'reviewed_by' => Auth::guard('admin')->id(),
                    'reviewed_at' => now(),
                    'review_comment' => null,
                ]);
                AdminOperationLog::log('批量审核通过文章: ' . $article->title, '文章管理');
            } else {
                $article->update([
                    'status' => Article::STATUS_DRAFT,
                    'reviewed_by' => Auth::guard('admin')->id(),
                    'reviewed_at' => now(),
                    'review_comment' => $reviewComment,
                ]);
                AdminOperationLog::log('批量驳回文章: ' . $article->title . ($reviewComment ? "，意见：{$reviewComment}" : ''), '文章管理');
            }
        }

        $count = $articles->count();
        if ($count === 0) {
            return back()->with('error', '所选文章中没有待审核的文章');
        }
        $message = $request->action === 'approve'
            ? "已批量通过 {$count} 篇文章"
            : "已批量驳回 {$count} 篇文章";

        return back()->with('success', $message);
    }

    public function approve(Article $article): RedirectResponse
    {
        if ($article->status !== Article::STATUS_REVIEW) {
            return back()->with('error', '仅待审核文章可执行此操作');
        }

        $article->update([
            'status' => Article::STATUS_PUBLISHED,
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
            'review_comment' => null,
        ]);
        AdminOperationLog::log('人工审核通过文章: ' . $article->title, '文章管理');

        return back()->with('success', '文章已审核通过并发布');
    }

    public function reject(Request $request, Article $article): RedirectResponse
    {
        if ($article->status !== Article::STATUS_REVIEW) {
            return back()->with('error', '仅待审核文章可执行此操作');
        }

        $comment = $request->input('review_comment', '');

        $article->update([
            'status' => Article::STATUS_DRAFT,
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
            'review_comment' => $comment,
        ]);
        AdminOperationLog::log('人工审核驳回文章: ' . $article->title . ($comment ? "，意见：{$comment}" : ''), '文章管理');

        return back()->with('success', '文章已驳回，已改为草稿状态');
    }

    public function destroy(Article $article): RedirectResponse
    {
        if ($article->cover_image) {
            Storage::disk('public')->delete($article->cover_image);
        }

        AdminOperationLog::log('删除文章: ' . $article->title, '文章管理');

        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', '文章已删除');
    }

    public function uploadImage(Request $request)
    {
        $request->validate(['file' => 'required|image|max:2048']);

        $path = $request->file('file')->store(config('admin.upload_path', 'uploads'), 'public');

        return response()->json([
            'location' => url(Storage::url($path)),
        ]);
    }
}
