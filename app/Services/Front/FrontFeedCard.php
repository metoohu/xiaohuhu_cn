<?php

namespace App\Services\Front;

use App\Models\Article;
use App\Models\UserArticle;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * 首页信息流卡片：统一文章与社区稿的展示字段。
 */
final class FrontFeedCard
{
    public const KIND_ARTICLE = 'article';

    public const KIND_COMMUNITY = 'community';

    private function __construct(
        private readonly string $kind,
        private readonly Article|UserArticle $model,
    ) {}

    public static function fromArticle(Article $article): self
    {
        return new self(self::KIND_ARTICLE, $article);
    }

    public static function fromUserArticle(UserArticle $userArticle): self
    {
        return new self(self::KIND_COMMUNITY, $userArticle);
    }

    /** @return self::KIND_* */
    public function kind(): string
    {
        return $this->kind;
    }

    public function title(): string
    {
        return (string) ($this->model->title ?? '');
    }

    /**
     * 纯文本摘要（已 strip_tags），供 Blade 再用 e() 转义。
     */
    public function excerpt(): string
    {
        if ($this->kind === self::KIND_ARTICLE) {
            /** @var Article $article */
            $article = $this->model;
            $raw = (string) ($article->content ?? '');
        } else {
            /** @var UserArticle $ua */
            $ua = $this->model;
            $raw = (string) ($ua->excerpt !== null && $ua->excerpt !== '' ? $ua->excerpt : ($ua->content_public ?? ''));
        }

        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($raw)) ?? '');

        return Str::limit($plain, 80) ?: '点击阅读全文';
    }

    public function url(): string
    {
        if ($this->kind === self::KIND_ARTICLE) {
            return route('front.articles.show', $this->model);
        }

        return route('front.community.show', $this->model);
    }

    public function clickNum(): int
    {
        return (int) ($this->model->click_num ?? 0);
    }

    /**
     * 合并排序用时刻：文章取 created_at，社区稿取 published_at（缺省回退 created_at）。
     */
    public function sortAt(): Carbon
    {
        if ($this->kind === self::KIND_ARTICLE) {
            /** @var Article $article */
            $article = $this->model;

            return $article->created_at ?? Carbon::createFromTimestampUTC(0);
        }

        /** @var UserArticle $ua */
        $ua = $this->model;

        return $ua->published_at ?? $ua->created_at ?? Carbon::createFromTimestampUTC(0);
    }
}
