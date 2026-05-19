<?php

namespace App\Services\Front;

use App\Models\Article;
use App\Models\UserArticle;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 将已发布的 articles 与 user_articles 合并：首页用内存 merge；分类列表用 UNION 分页（同排序语义）。
 */
final class FrontHomeFeedMerger
{
    /**
     * 首页双源：内存合并后截断。
     *
     * @param  \Illuminate\Support\Collection<int, Article>|array<int, Article>  $articles
     * @param  \Illuminate\Support\Collection<int, UserArticle>|array<int, UserArticle>  $userArticles
     * @return Collection<int, FrontFeedCard>
     */
    public function merge(Collection|array $articles, Collection|array $userArticles, int $limit): Collection
    {
        $rows = collect();

        foreach (Collection::wrap($articles) as $article) {
            if (! $article instanceof Article) {
                continue;
            }
            $card = FrontFeedCard::fromArticle($article);
            $rows->push([
                'sort' => $card->sortAt(),
                'id' => (int) $article->getKey(),
                'card' => $card,
            ]);
        }

        foreach (Collection::wrap($userArticles) as $ua) {
            if (! $ua instanceof UserArticle) {
                continue;
            }
            $card = FrontFeedCard::fromUserArticle($ua);
            $rows->push([
                'sort' => $card->sortAt(),
                'id' => (int) $ua->getKey(),
                'card' => $card,
            ]);
        }

        // 先按 sort 时刻降序，再按各自表主键 id 降序（用 Unix 秒比较，避免 SQLite 返回字符串导致 <=> 不稳定）
        return $rows->sort(function (array $a, array $b): int {
            $tsA = $a['sort']->getTimestamp();
            $tsB = $b['sort']->getTimestamp();
            $cmp = $tsB <=> $tsA;
            if ($cmp !== 0) {
                return $cmp;
            }

            return $b['id'] <=> $a['id'];
        })->pluck('card')->take($limit)->values();
    }

    /**
     * 分类详情：多 category_id（父+子）下双源列表分页，按 Article.created_at / UserArticle.published_at 混排。
     * 使用 UNION 子查询 + forPage，避免全量载入；总数为两侧 count 之和。
     *
     * @param  list<int>  $categoryIds
     */
    public function paginateCategoryDualSource(array $categoryIds): LengthAwarePaginator
    {
        $perPage = max(1, (int) config('front.article_per_page', 15));
        $currentPage = Paginator::resolveCurrentPage('page');

        $articleCount = Article::query()
            ->where('status', Article::STATUS_PUBLISHED)
            ->whereIn('category_id', $categoryIds)
            ->count();

        $userArticleCount = UserArticle::query()
            ->where('status', UserArticle::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->whereIn('category_id', $categoryIds)
            ->count();

        $total = $articleCount + $userArticleCount;

        $articlePart = Article::query()
            ->where('status', Article::STATUS_PUBLISHED)
            ->whereIn('category_id', $categoryIds)
            ->select([
                'articles.id',
                DB::raw("'article' as feed_kind"),
                'articles.created_at as feed_sort_at',
            ]);

        $userArticlePart = UserArticle::query()
            ->where('status', UserArticle::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->whereIn('category_id', $categoryIds)
            ->select([
                'user_articles.id',
                DB::raw("'user_article' as feed_kind"),
                'user_articles.published_at as feed_sort_at',
            ]);

        $union = $articlePart->unionAll($userArticlePart);

        $rows = DB::query()
            ->fromSub($union, 'feed')
            ->orderByDesc('feed_sort_at')
            ->forPage($currentPage, $perPage)
            ->get();

        $articleIds = $rows->where('feed_kind', 'article')->pluck('id')->all();
        $userArticleIds = $rows->where('feed_kind', 'user_article')->pluck('id')->all();

        $articlesById = Article::query()
            ->whereIn('id', $articleIds)
            ->with('category')
            ->get()
            ->keyBy('id');

        $userArticlesById = UserArticle::query()
            ->whereIn('id', $userArticleIds)
            ->with('category')
            ->get()
            ->keyBy('id');

        $items = $rows->map(function ($row) use ($articlesById, $userArticlesById) {
            if ($row->feed_kind === 'article') {
                return (object) [
                    'type' => 'article',
                    'article' => $articlesById->get($row->id),
                ];
            }

            return (object) [
                'type' => 'user_article',
                'userArticle' => $userArticlesById->get($row->id),
            ];
        })->filter(fn ($item) => ($item->type === 'article' && $item->article) || ($item->type === 'user_article' && $item->userArticle));

        return new LengthAwarePaginator(
            $items->values(),
            $total,
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }
}
