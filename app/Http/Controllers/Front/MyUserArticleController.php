<?php

namespace App\Http\Controllers\Front;

use App\Exceptions\ForbiddenContentException;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\UserArticle;
use App\Services\UserArticle\UserArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * 前台会员：我的社区稿（草稿/待审/已发布/驳回）。
 */
class MyUserArticleController extends Controller
{
    public function __construct(
        protected UserArticleService $userArticleService
    ) {}

    public function index(Request $request): RedirectResponse
    {
        return $this->redirectToProfileArticles($request->input('tab', 'all'));
    }

    public function create(): View
    {
        $categories = Category::getSubmitSelectOptions();

        return view('front.my.articles.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:'.$this->userArticleService->maxRequestContentLength(),
            'tags_csv' => 'nullable|string|max:200',
            'action' => 'nullable|in:draft,submit',
        ]);

        $tags = $this->tagsFromCsv($request);

        try {
            $article = $this->userArticleService->createDraft(auth()->user(), [
                'category_id' => (int) $request->category_id,
                'title' => (string) $request->title,
                'content' => (string) $request->content,
                'tags' => $tags,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (ForbiddenContentException $e) {
            return $this->forbiddenContentRedirect($e);
        }

        if ($request->input('action') === 'submit') {
            try {
                $this->userArticleService->submitForReview($article, auth()->user());
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->route('front.my.articles.edit', $article)->withInput()->withErrors($e->errors());
            } catch (ForbiddenContentException $e) {
                return $this->forbiddenContentRedirect($e);
            }

            return $this->redirectToProfileArticles(UserArticle::STATUS_PENDING_REVIEW, '已提交审核');
        }

        return $this->redirectToProfileArticles(UserArticle::STATUS_DRAFT, '草稿已保存');
    }

    public function edit(UserArticle $userArticle): View|RedirectResponse
    {
        $this->authorizeOwn($userArticle);
        if ($userArticle->status === UserArticle::STATUS_PENDING_REVIEW) {
            return $this->redirectToProfileArticles(UserArticle::STATUS_PENDING_REVIEW, null)
                ->with('error', '审核中的稿件请撤回后再编辑');
        }
        $categories = Category::getSubmitSelectOptions();
        $userArticle->load(['tags', 'category']);

        return view('front.my.articles.edit', compact('userArticle', 'categories'));
    }

    public function update(Request $request, UserArticle $userArticle): RedirectResponse
    {
        $this->authorizeOwn($userArticle);
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'nullable|string|max:200',
            'content' => 'nullable|string|max:'.$this->userArticleService->maxRequestContentLength(),
            'tags_csv' => 'nullable|string|max:200',
            'action' => 'nullable|in:save,submit',
        ]);

        $tags = $this->tagsFromCsv($request);

        try {
            if ($userArticle->status === UserArticle::STATUS_PUBLISHED) {
                $this->userArticleService->updatePendingRevision($userArticle, auth()->user(), [
                    'title' => $request->has('title') ? (string) $request->title : null,
                    'content' => (string) $request->input('content', ''),
                    'tags' => $tags,
                ]);
            } else {
                $this->userArticleService->updateDraft($userArticle, auth()->user(), [
                    'category_id' => $request->has('category_id') ? (int) $request->category_id : null,
                    'title' => $request->has('title') ? (string) $request->title : null,
                    'content' => $request->has('content') ? (string) $request->content : null,
                    'tags' => $tags,
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (ForbiddenContentException $e) {
            return $this->forbiddenContentRedirect($e);
        }

        if ($request->input('action') === 'submit') {
            try {
                $this->userArticleService->submitForReview($userArticle->fresh(), auth()->user());
            } catch (\Illuminate\Validation\ValidationException $e) {
                return back()->withInput()->withErrors($e->errors());
            } catch (ForbiddenContentException $e) {
                return $this->forbiddenContentRedirect($e);
            }

            return $this->redirectToProfileArticles(UserArticle::STATUS_PENDING_REVIEW, '已提交审核');
        }

        $userArticle->refresh();
        $tab = $userArticle->status === UserArticle::STATUS_REJECTED
            ? UserArticle::STATUS_REJECTED
            : ($userArticle->status === UserArticle::STATUS_PUBLISHED
                ? UserArticle::STATUS_PUBLISHED
                : UserArticle::STATUS_DRAFT);

        return $this->redirectToProfileArticles($tab, '已保存');
    }

    public function destroy(UserArticle $userArticle): RedirectResponse
    {
        $this->authorizeOwn($userArticle);
        try {
            $this->userArticleService->deleteForUser($userArticle, auth()->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return $this->redirectToProfileArticles('all', '已删除');
    }

    public function submit(UserArticle $userArticle): RedirectResponse
    {
        $this->authorizeOwn($userArticle);
        try {
            $this->userArticleService->submitForReview($userArticle, auth()->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (ForbiddenContentException $e) {
            return $this->forbiddenContentRedirect($e);
        }

        return $this->redirectToProfileArticles(UserArticle::STATUS_PENDING_REVIEW, '已提交审核');
    }

    public function withdraw(UserArticle $userArticle): RedirectResponse
    {
        $this->authorizeOwn($userArticle);
        try {
            $this->userArticleService->withdraw($userArticle, auth()->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $tab = $userArticle->fresh()->status === UserArticle::STATUS_PUBLISHED
            ? UserArticle::STATUS_PUBLISHED
            : UserArticle::STATUS_DRAFT;

        return $this->redirectToProfileArticles($tab, '已撤回至草稿或保持已发布');
    }

    /**
     * 会员投稿正文编辑器（TinyMCE）内嵌图片上传，逻辑对齐后台文章上传。
     */
    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|image|max:2048',
            ], [
                'file.required' => '请选择要上传的图片',
                'file.image' => '请上传图片文件（支持 jpg、png、gif、webp 等格式）',
                'file.max' => '图片大小不能超过 2MB',
            ]);

            $path = self::storeUserArticleImage($request->file('file'));

            return response()->json([
                'location' => url(Storage::url($path)),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => '图片验证失败',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('用户投稿图片上传失败: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => '上传失败：'.(config('app.debug') ? $e->getMessage() : '服务器处理异常，请重试'),
            ], 500);
        }
    }

    /**
     * 写入 public 磁盘并校验文件确实存在（与后台 ArticleController 策略一致）。
     */
    private static function storeUserArticleImage(\Illuminate\Http\UploadedFile $file): string
    {
        $uploadPath = config('admin.upload_path', 'uploads');
        Storage::disk('public')->makeDirectory($uploadPath);

        $path = $file->store($uploadPath, 'public');

        if ($path === false || $path === '') {
            throw new \RuntimeException('图片上传失败，请检查 storage 目录权限');
        }

        if (! Storage::disk('public')->exists($path)) {
            throw new \RuntimeException('图片写入验证失败，请确认 storage/app/public 目录存在且可写入');
        }

        return $path;
    }

    protected function authorizeOwn(UserArticle $userArticle): void
    {
        if ((int) $userArticle->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    /**
     * 从逗号分隔输入解析标签名列表。
     *
     * @return list<string>
     */
    protected function tagsFromCsv(Request $request): array
    {
        $csv = trim((string) $request->input('tags_csv', ''));

        if ($csv === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/[,，]/u', $csv) ?: [])));
    }

    /**
     * 违禁词拦截：回退表单并附带扫描结果供前端展示。
     */
    protected function forbiddenContentRedirect(ForbiddenContentException $e): RedirectResponse
    {
        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['forbidden_content' => $e->result->messages])
            ->with('forbidden_scan', $e->result->toArray());
    }

    /**
     * 文章列表已并入个人中心 Tab，统一跳转到 profile?page=articles。
     */
    protected function redirectToProfileArticles(string $articleTab, ?string $message): RedirectResponse
    {
        $params = ['page' => 'articles'];
        if ($articleTab !== 'all' && in_array($articleTab, UserArticle::statusKeys(), true)) {
            $params['article_tab'] = $articleTab;
        }

        $redirect = redirect()->route('front.my.profile', $params);
        if ($message !== null) {
            $redirect->with('success', $message);
        }

        return $redirect;
    }
}
