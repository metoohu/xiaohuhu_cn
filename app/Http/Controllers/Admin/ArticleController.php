<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminOperationLog;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * 上傳封面圖並驗證寫入成功（解決生產環境 store 回傳路徑但檔案未實際寫入的問題）
     */
    private static function storeCoverImage(\Illuminate\Http\UploadedFile $file): string
    {
        $uploadPath = config('admin.upload_path', 'uploads');
        Storage::disk('public')->makeDirectory($uploadPath);

        $path = $file->store($uploadPath, 'public');

        if ($path === false || $path === '') {
            throw new \RuntimeException('封面圖上傳失敗，請檢查 storage 目錄權限');
        }

        if (! Storage::disk('public')->exists($path)) {
            throw new \RuntimeException('封面圖寫入驗證失敗，請確認 storage/app/public 目錄存在且可寫入');
        }

        return $path;
    }

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

        $categories = Category::getTreeOptions();

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::getTreeOptions();

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
            'click_num' => 'nullable|integer|min:0',
        ], [
            'cover_image.image' => '封面图必须是图片格式（jpg、png、gif、webp）',
            'cover_image.max' => '封面图大小不能超过 2MB',
        ]);

        $data = $request->only('title', 'content', 'category_id', 'status');
        $data['click_num'] = (int) ($request->input('click_num', 0));
        $data['admin_user_id'] = Auth::guard('admin')->id();

        try {
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = self::storeCoverImage($request->file('cover_image'));
            }
        } catch (\Throwable $e) {
            Log::error('封面圖上傳失敗: ' . $e->getMessage(), ['exception' => $e]);

            return redirect()->back()->withInput()->withErrors(['cover_image' => $e->getMessage()]);
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
        $categories = Category::getTreeOptions();

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
            'click_num' => 'nullable|integer|min:0',
        ], [
            'cover_image.image' => '封面图必须是图片格式（jpg、png、gif、webp）',
            'cover_image.max' => '封面图大小不能超过 2MB',
        ]);

        $data = $request->only('title', 'content', 'category_id', 'status');
        $data['click_num'] = (int) ($request->input('click_num', 0));

        try {
            if ($request->hasFile('cover_image')) {
                if ($article->cover_image) {
                    Storage::disk('public')->delete($article->cover_image);
                }
                $data['cover_image'] = self::storeCoverImage($request->file('cover_image'));
            }
        } catch (\Throwable $e) {
            Log::error('封面圖上傳失敗: ' . $e->getMessage(), ['exception' => $e]);

            return redirect()->back()->withInput()->withErrors(['cover_image' => $e->getMessage()]);
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
        try {
            $request->validate([
                'file' => 'required|image|max:2048',
            ], [
                'file.required' => '请选择要上传的图片',
                'file.image' => '请上传图片文件（支持 jpg、png、gif、webp 等格式）',
                'file.max' => '图片大小不能超过 2MB',
            ]);

            $path = self::storeCoverImage($request->file('file'));

            return response()->json([
                'location' => url(Storage::url($path)),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => '图片验证失败',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('文章图片上传失败: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => '上传失败：' . (config('app.debug') ? $e->getMessage() : '服务器处理异常，请重试'),
            ], 500);
        }
    }
}
