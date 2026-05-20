<?php

namespace App\Services\ForbiddenWord;

use App\Services\ForbiddenWord\Dto\ForbiddenWordHit;

/**
 * 多模式匹配：精确子串 + 简单模糊（* 通配）；命中经豁免表过滤。
 */
class ForbiddenWordScanner
{
    public function __construct(
        protected TextNormalizer $normalizer,
        protected ForbiddenWordFieldMapper $fieldMapper,
    ) {}

    /**
     * @param  array{words: list<array<string, mixed>>, allowlist: list<string>}  $dictionary
     * @return list<ForbiddenWordHit>
     */
    public function scanText(string $normalizedText, string $fieldKey, array $dictionary): array
    {
        if ($normalizedText === '') {
            return [];
        }

        $hits = [];
        $fieldRole = $this->fieldMapper->roleForField($fieldKey);

        foreach ($dictionary['words'] as $entry) {
            $pattern = $this->normalizer->normalize((string) ($entry['word'] ?? ''));
            if ($pattern === '') {
                continue;
            }

            $positions = ($entry['match_type'] ?? 'exact') === 'fuzzy'
                ? $this->findFuzzyPositions($normalizedText, $pattern)
                : $this->findExactPositions($normalizedText, $pattern);

            foreach ($positions as [$start, $end]) {
                if ($this->isAllowlisted($normalizedText, $start, $end, $dictionary['allowlist'] ?? [])) {
                    continue;
                }

                $hits[] = new ForbiddenWordHit(
                    field: $fieldKey,
                    word: (string) ($entry['word'] ?? $pattern),
                    categorySlug: (string) ($entry['category_slug'] ?? ''),
                    categoryName: (string) ($entry['category_name'] ?? ''),
                    level: (string) ($entry['level'] ?? 'block'),
                    start: $start,
                    end: $end,
                    fieldRole: $fieldRole,
                    wordId: (int) ($entry['id'] ?? 0),
                    replacement: $entry['replacement'] ?? null,
                );
            }
        }

        return $hits;
    }

    /**
     * @return list<array{0: int, 1: int}>
     */
    protected function findExactPositions(string $text, string $needle): array
    {
        $positions = [];
        $offset = 0;
        $len = mb_strlen($needle);

        while (true) {
            $pos = mb_strpos($text, $needle, $offset);
            if ($pos === false) {
                break;
            }
            $positions[] = [$pos, $pos + $len];
            $offset = $pos + max(1, $len);
        }

        return $positions;
    }

    /**
     * @return list<array{0: int, 1: int}>
     */
    protected function findFuzzyPositions(string $text, string $pattern): array
    {
        $regex = '#'.preg_quote($pattern, '#').'#u';
        $regex = str_replace('\*', '.+?', $regex);

        if (! preg_match_all($regex, $text, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $positions = [];
        foreach ($matches[0] as $match) {
            $byteOffset = $match[1];
            $matched = $match[0];
            $start = mb_strlen(mb_substr($text, 0, $byteOffset));
            $positions[] = [$start, $start + mb_strlen($matched)];
        }

        return $positions;
    }

    /**
     * @param  list<string>  $allowlist
     */
    protected function isAllowlisted(string $text, int $start, int $end, array $allowlist): bool
    {
        foreach ($allowlist as $phrase) {
            if ($phrase === '') {
                continue;
            }
            $offset = 0;
            while (true) {
                $pos = mb_strpos($text, $phrase, $offset);
                if ($pos === false) {
                    break;
                }
                $phraseEnd = $pos + mb_strlen($phrase);
                if ($pos <= $start && $phraseEnd >= $end) {
                    return true;
                }
                $offset = $pos + 1;
            }
        }

        return false;
    }
}
