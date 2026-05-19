<?php

namespace App\Services\UserArticle;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserArticle;
use App\Services\EmotionalArticle\EmotionalArticleHtmlSanitizer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * 用户社区稿：草稿、提交审核、撤回、标签同步与管理员审核编排。
 */
class UserArticleService
{
    public function __construct(
        protected EmotionalArticleHtmlSanitizer $htmlSanitizer
    ) {}

    public function cfg(string $key): int
    {
        return (int) config('front.user_article.'.$key);
    }

    /**
     * HTTP 请求体「正文」字段允许的最大字符数（含 HTML 标签），需大于纯文本上限 config content_max。
     */
    public function maxRequestContentLength(): int
    {
        return $this->cfg('content_max') * 20 + 65536;
    }

    /** 与后台 TinyMCE 输出对齐：白名单标签 + strip_tags */
    public function sanitizeBody(string $raw): string
    {
        return $this->htmlSanitizer->sanitize($raw);
    }

    /**
     * 用于字数校验、摘要、是否「空正文」的纯文本度量（去掉 HTML 与多余空白）。
     */
    public function plainBodyForMetrics(string $html): string
    {
        $plain = strip_tags($html);
        $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace('/\s+/u', ' ', $plain) ?? '';

        return trim($plain);
    }

    public function buildExcerpt(string $htmlOrPlain): string
    {
        $plain = $this->plainBodyForMetrics($htmlOrPlain);

        return Str::limit($plain, 200, '…');
    }

    /**
     * @param  list<string>  $tagNames
     *
     * @throws ValidationException
     */
    public function validateTagNames(array $tagNames): void
    {
        $max = $this->cfg('max_tags');
        if (count($tagNames) > $max) {
            throw ValidationException::withMessages(['tags' => "最多绑定 {$max} 个标签"]);
        }
        foreach ($tagNames as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            if (mb_strlen($name) > 32) {
                throw ValidationException::withMessages(['tags' => '单个标签名称过长']);
            }
        }
    }

    /**
     * @param  list<string>  $tagNames
     */
    public function syncTags(UserArticle $article, array $tagNames): void
    {
        $this->validateTagNames($tagNames);
        $ids = [];
        foreach ($tagNames as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $slugBase = Str::slug($name);
            if ($slugBase === '') {
                $slugBase = 'tag';
            }
            $slug = $slugBase;
            $n = 0;
            while (Tag::where('slug', $slug)->whereNotIn('id', $ids)->exists()) {
                $slug = $slugBase.'-'.(++$n);
            }
            $tag = Tag::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
            if ($tag->name !== $name) {
                $tag->name = $name;
                $tag->save();
            }
            $ids[] = $tag->id;
        }
        $article->tags()->sync(array_values(array_unique($ids)));
    }

    /**
     * @throws ValidationException
     */
    public function assertCategoryAllowsSubmit(int $categoryId): Category
    {
        $cat = Category::query()->whereKey($categoryId)->firstOrFail();
        if ((int) $cat->status !== Category::STATUS_ENABLED || ! $cat->user_can_submit) {
            throw ValidationException::withMessages(['category_id' => '所选分类不允许用户投稿或未启用']);
        }

        return $cat;
    }

    /**
     * @throws ValidationException
     */
    public function validateTitleAndContent(string $title, string $bodyHtml): void
    {
        $tMin = $this->cfg('title_min');
        $tMax = $this->cfg('title_max');
        $cMin = $this->cfg('content_min');
        $cMax = $this->cfg('content_max');
        if (mb_strlen($title) < $tMin || mb_strlen($title) > $tMax) {
            throw ValidationException::withMessages(['title' => "标题长度需在 {$tMin}～{$tMax} 字之间"]);
        }
        $plainLen = mb_strlen($this->plainBodyForMetrics($bodyHtml));
        if ($plainLen < $cMin || $plainLen > $cMax) {
            throw ValidationException::withMessages(['content' => "正文长度需在 {$cMin}～{$cMax} 字之间（按纯文本计）"]);
        }
    }

    /** 当日已成功占用额度的投稿数（submitted_at 有值且未撤回清空） */
    public function countSubmitsToday(User $user): int
    {
        return UserArticle::query()
            ->where('user_id', $user->id)
            ->whereDate('submitted_at', Carbon::today())
            ->whereNotNull('submitted_at')
            ->count();
    }

    /**
     * @throws ValidationException
     */
    public function assertUnderDailySubmitLimit(User $user): void
    {
        $limit = $this->cfg('daily_submit_limit');
        if ($this->countSubmitsToday($user) >= $limit) {
            throw ValidationException::withMessages(['submit' => "每日最多提交 {$limit} 篇审核，请明日再试"]);
        }
    }

    /**
     * @param  array{category_id:int,title:string,content:string,tags?:list<string>}  $data
     */
    public function createDraft(User $user, array $data): UserArticle
    {
        $categoryId = (int) $data['category_id'];
        $this->assertCategoryAllowsSubmit($categoryId);
        $title = trim((string) $data['title']);
        $body = $this->sanitizeBody((string) $data['content']);
        $this->validateTitleAndContent($title, $body);

        return DB::transaction(function () use ($user, $categoryId, $title, $body, $data) {
            $article = UserArticle::create([
                'user_id' => $user->id,
                'category_id' => $categoryId,
                'title' => $title,
                'content_pending' => $body,
                'content_public' => null,
                'status' => UserArticle::STATUS_DRAFT,
                'is_original' => true,
                'excerpt' => $this->buildExcerpt($body),
            ]);
            $this->syncTags($article, $data['tags'] ?? []);

            return $article->fresh(['tags', 'category']);
        });
    }

    /**
     * @param  array{category_id?:int,title?:string,content?:string,tags?:list<string>}  $data
     */
    public function updateDraft(UserArticle $article, User $user, array $data): UserArticle
    {
        $this->assertOwner($article, $user);
        if (! in_array($article->status, [UserArticle::STATUS_DRAFT, UserArticle::STATUS_REJECTED], true)) {
            throw ValidationException::withMessages(['status' => '当前状态不可直接保存为草稿修改']);
        }

        $categoryId = isset($data['category_id']) ? (int) $data['category_id'] : (int) $article->category_id;
        $this->assertCategoryAllowsSubmit($categoryId);
        $title = isset($data['title']) ? trim((string) $data['title']) : $article->title;
        $body = isset($data['content']) ? $this->sanitizeBody((string) $data['content']) : (string) $article->content_pending;
        $this->validateTitleAndContent($title, $body);

        return DB::transaction(function () use ($article, $categoryId, $title, $body, $data) {
            $article->fill([
                'category_id' => $categoryId,
                'title' => $title,
                'content_pending' => $body,
                'excerpt' => $this->buildExcerpt($body),
            ]);
            $article->save();
            if (array_key_exists('tags', $data)) {
                $this->syncTags($article, $data['tags'] ?? []);
            }

            return $article->fresh(['tags', 'category']);
        });
    }

    /**
     * 已发布用户发起「修改再审」：仅更新待审正文，不改变对外正文。
     *
     * @param  array{title?:string,content:string,tags?:list<string>}  $data
     */
    public function updatePendingRevision(UserArticle $article, User $user, array $data): UserArticle
    {
        $this->assertOwner($article, $user);
        if ($article->status !== UserArticle::STATUS_PUBLISHED) {
            throw ValidationException::withMessages(['status' => '仅已发布稿件可提交修改再审']);
        }
        $title = isset($data['title']) ? trim((string) $data['title']) : $article->title;
        $body = $this->sanitizeBody((string) $data['content']);
        $this->validateTitleAndContent($title, $body);

        return DB::transaction(function () use ($article, $title, $body, $data) {
            $article->fill([
                'title' => $title,
                'content_pending' => $body,
            ]);
            $article->save();
            if (array_key_exists('tags', $data)) {
                $this->syncTags($article, $data['tags'] ?? []);
            }

            return $article->fresh(['tags', 'category']);
        });
    }

    /**
     * 提交审核：草稿/驳回 -> 待审；已发布且有新正文 -> 待审（对外仍展示旧版）。
     *
     * @throws ValidationException
     */
    public function submitForReview(UserArticle $article, User $user): UserArticle
    {
        $this->assertOwner($article, $user);
        if ($article->status === UserArticle::STATUS_PENDING_REVIEW) {
            throw ValidationException::withMessages(['status' => '已在审核中']);
        }

        $pending = (string) $article->content_pending;
        $this->validateTitleAndContent($article->title, $pending);

        if (in_array($article->status, [UserArticle::STATUS_DRAFT, UserArticle::STATUS_REJECTED], true)) {
            $this->assertUnderDailySubmitLimit($user);
        }

        if ($article->status === UserArticle::STATUS_PUBLISHED) {
            if ($this->plainBodyForMetrics($pending) === '' || $pending === (string) $article->content_public) {
                throw ValidationException::withMessages(['content' => '请修改正文后再提交再审']);
            }
            $this->assertUnderDailySubmitLimit($user);
        }

        return DB::transaction(function () use ($article) {
            $article->status = UserArticle::STATUS_PENDING_REVIEW;
            $article->submitted_at = now();
            $article->rejection_reason = null;
            $article->save();

            return $article->fresh(['tags', 'category']);
        });
    }

    public function withdraw(UserArticle $article, User $user): UserArticle
    {
        $this->assertOwner($article, $user);
        if ($article->status !== UserArticle::STATUS_PENDING_REVIEW) {
            throw ValidationException::withMessages(['status' => '仅待审核稿件可撤回']);
        }

        return DB::transaction(function () use ($article) {
            if ($article->content_public) {
                $article->status = UserArticle::STATUS_PUBLISHED;
            } else {
                $article->status = UserArticle::STATUS_DRAFT;
            }
            $article->submitted_at = null;
            $article->save();

            return $article->fresh(['tags', 'category']);
        });
    }

    public function deleteForUser(UserArticle $article, User $user): void
    {
        $this->assertOwner($article, $user);
        if (! in_array($article->status, [UserArticle::STATUS_DRAFT, UserArticle::STATUS_REJECTED], true)) {
            throw ValidationException::withMessages(['status' => '仅草稿或已驳回稿件可删除']);
        }
        $article->tags()->detach();
        $article->delete();
    }

    public function approve(UserArticle $article, int $adminUserId): UserArticle
    {
        return DB::transaction(function () use ($article, $adminUserId) {
            $pending = (string) $article->content_pending;
            if ($this->plainBodyForMetrics($pending) === '') {
                throw ValidationException::withMessages(['content' => '待审正文为空，无法通过']);
            }
            $article->content_public = $pending;
            $article->content_pending = null;
            $article->excerpt = $this->buildExcerpt($article->content_public);
            $article->status = UserArticle::STATUS_PUBLISHED;
            $article->reviewed_by = $adminUserId;
            $article->reviewed_at = now();
            $article->rejection_reason = null;
            if ($article->published_at === null) {
                $article->published_at = now();
            }
            $article->save();

            return $article->fresh(['tags', 'category', 'user']);
        });
    }

    /**
     * 驳回：从未发布则进入 rejected；已上线后再审驳回则回到 published 并保留对外正文。
     */
    public function reject(UserArticle $article, int $adminUserId, string $reason): UserArticle
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw ValidationException::withMessages(['rejection_reason' => '请填写驳回原因']);
        }

        return DB::transaction(function () use ($article, $adminUserId, $reason) {
            $article->reviewed_by = $adminUserId;
            $article->reviewed_at = now();
            $article->rejection_reason = $reason;
            if ($article->content_public !== null && $article->content_public !== '') {
                $article->status = UserArticle::STATUS_PUBLISHED;
            } else {
                $article->status = UserArticle::STATUS_REJECTED;
            }
            $article->save();

            return $article->fresh(['tags', 'category', 'user']);
        });
    }

    protected function assertOwner(UserArticle $article, User $user): void
    {
        if ((int) $article->user_id !== (int) $user->id) {
            abort(403);
        }
    }
}
