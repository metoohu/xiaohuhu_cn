<?php

namespace App\Models;

use App\Models\Admin\AdminUser;
use App\Services\EmotionalArticle\EmotionalArticleHtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 用户社区稿（B2：独立表，审核通过不入 articles）。
 */
class UserArticle extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'content_public',
        'content_pending',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'published_at',
        'is_original',
        'excerpt',
        'click_num',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_original' => 'boolean',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'reviewed_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'user_article_tag');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** 前台列表：已发布且可排序 */
    public function scopePublishedForFront(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at');
    }

    /** 详情页展示用正文（再审期间仍读线上版） */
    public function displayContent(): string
    {
        return (string) ($this->content_public ?? '');
    }

    /**
     * 详情页输出 HTML：入库已消毒，此处再白名单过滤一次，避免历史数据或异常写入导致 XSS。
     * 无标签的历史纯文本仍按换行展示。
     */
    public function displayContentSafeHtml(): string
    {
        $raw = $this->displayContent();
        if (trim($raw) === '') {
            return '';
        }

        return self::formatStoredBodyForHtml($raw);
    }

    /**
     * 后台审核页预览：空正文返回占位 HTML，否则与前台同源消毒 + 纯文本换行规则。
     */
    public static function adminBodyPreviewHtml(?string $stored): string
    {
        if ($stored === null || trim((string) $stored) === '') {
            return '<span class="text-slate-400">（空）</span>';
        }

        $html = self::formatStoredBodyForHtml((string) $stored);

        return $html !== '' ? $html : '<span class="text-slate-400">（空）</span>';
    }

    /**
     * 将库中正文转为可放入 Blade 的安全 HTML（TinyMCE 白名单 + 纯文本 nl2br）。
     */
    private static function formatStoredBodyForHtml(string $raw): string
    {
        $safe = app(EmotionalArticleHtmlSanitizer::class)->sanitize($raw);
        if ($safe === '') {
            return '';
        }
        if (! preg_match('/<[a-z][a-z0-9]*\b/i', $safe)) {
            return nl2br(e($safe), false);
        }

        return $safe;
    }

    /**
     * @return list<string>
     */
    public static function statusKeys(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_REVIEW,
            self::STATUS_PUBLISHED,
            self::STATUS_REJECTED,
        ];
    }
}
