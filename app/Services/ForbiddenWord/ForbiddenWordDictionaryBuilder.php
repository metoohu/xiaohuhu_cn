<?php

namespace App\Services\ForbiddenWord;

use App\Models\ForbiddenWord;
use App\Models\ForbiddenWordAllowlist;
use App\Models\ForbiddenWordCategory;
use Illuminate\Support\Facades\Cache;

/**
 * 从数据库构建词典快照并缓存（版本号随词条更新时间变化）。
 */
class ForbiddenWordDictionaryBuilder
{
    /**
     * @return array{words: list<array<string, mixed>>, allowlist: list<string>}
     */
    public function build(): array
    {
        $key = config('forbidden_words.cache_key').':'. $this->version();

        return Cache::remember(
            $key,
            (int) config('forbidden_words.cache_ttl', 3600),
            fn () => $this->buildFresh()
        );
    }

    public function version(): string
    {
        $wordTs = ForbiddenWord::query()->max('updated_at') ?? '0';
        $allowTs = ForbiddenWordAllowlist::query()->max('updated_at') ?? '0';

        return md5($wordTs.'|'.$allowTs);
    }

    public function forgetCache(): void
    {
        Cache::forget(config('forbidden_words.cache_key').':'.$this->version());
    }

    /**
     * @return array{words: list<array<string, mixed>>, allowlist: list<string>}
     */
    protected function buildFresh(): array
    {
        $categories = ForbiddenWordCategory::query()->get()->keyBy('id');

        $words = ForbiddenWord::query()
            ->enabled()
            ->with('category')
            ->get()
            ->map(function (ForbiddenWord $row) use ($categories) {
                $cat = $categories->get($row->category_id) ?? $row->category;

                return [
                    'id' => $row->id,
                    'word' => $row->word,
                    'match_type' => $row->match_type ?: 'exact',
                    'replacement' => $row->replacement,
                    'category_slug' => $cat?->slug ?? '',
                    'category_name' => $cat?->name ?? '',
                    'level' => $cat?->level ?? 'block',
                ];
            })
            ->values()
            ->all();

        $allowlist = ForbiddenWordAllowlist::query()
            ->enabled()
            ->orderByRaw('LENGTH(phrase) DESC')
            ->pluck('phrase')
            ->map(fn ($p) => app(TextNormalizer::class)->normalize((string) $p))
            ->filter()
            ->values()
            ->all();

        return ['words' => $words, 'allowlist' => $allowlist];
    }
}
