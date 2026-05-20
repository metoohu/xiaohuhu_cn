<?php

namespace App\Services\ForbiddenWord;

use App\Exceptions\ForbiddenContentException;
use App\Models\ForbiddenWordViolation;
use App\Services\ForbiddenWord\Dto\ForbiddenWordHit;
use App\Services\ForbiddenWord\Dto\ScanResult;

/**
 * 统一违禁词扫描入口：实时 preview 用 scan；写入口用 assertOrReplace。
 */
class ForbiddenContentService
{
    public function __construct(
        protected ForbiddenWordDictionaryBuilder $dictionaryBuilder,
        protected ForbiddenWordScanner $scanner,
        protected ForbiddenWordPolicy $policy,
        protected TextNormalizer $normalizer,
    ) {}

    /**
     * 实时扫描（M6/M8）：不写违规记录，仅返回命中与决策。
     *
     * @param  array<string, string>  $fields
     */
    public function scan(array $fields, string $context): ScanResult
    {
        $dictionary = $this->dictionaryBuilder->build();
        $hitsByField = $this->collectHits($fields, $dictionary);
        $decision = $this->policy->decide($hitsByField, $fields);

        return $this->buildScanResult($hitsByField, $decision);
    }

    /**
     * 写入口强校验：拦截抛异常；调性替换返回新字段。
     *
     * @param  array<string, string>  $fields
     * @return array{fields: array<string, string>, messages: list<string>}
     */
    public function assertOrReplace(
        array $fields,
        string $context,
        ?string $titleSnapshot = null,
        ?string $contentType = null,
        ?int $contentId = null,
    ): array {
        $result = $this->scan($fields, $context);

        if (! $result->allowed || $result->action === 'block') {
            $this->recordViolations($result, $fields, $context, $titleSnapshot, $contentType, $contentId);

            throw new ForbiddenContentException($result);
        }

        if ($result->action === 'replace') {
            $merged = array_merge($fields, $result->replacedFields);

            return [
                'fields' => $merged,
                'messages' => $result->messages,
            ];
        }

        return ['fields' => $fields, 'messages' => []];
    }

    /**
     * @param  array<string, string>  $fields
     * @param  array{words: list<array<string, mixed>>, allowlist: list<string>}  $dictionary
     * @return array<string, list<ForbiddenWordHit>>
     */
    protected function collectHits(array $fields, array $dictionary): array
    {
        $hitsByField = [];

        foreach ($fields as $key => $raw) {
            $text = $this->normalizer->normalize((string) $raw);
            $fieldHits = $this->scanner->scanText($text, (string) $key, $dictionary);
            if ($fieldHits !== []) {
                $hitsByField[$key] = $fieldHits;
            }
        }

        return $hitsByField;
    }

    /**
     * @param  array<string, list<ForbiddenWordHit>>  $hitsByField
     */
    protected function buildScanResult(array $hitsByField, \App\Services\ForbiddenWord\Dto\ForbiddenWordDecision $decision): ScanResult
    {
        $flat = [];
        foreach ($hitsByField as $hits) {
            foreach ($hits as $hit) {
                $flat[] = $hit->toArray();
            }
        }

        return new ScanResult(
            allowed: $decision->allowed,
            action: $decision->action,
            hits: $flat,
            replacedFields: $decision->replacedFields,
            messages: $decision->messages,
        );
    }

    /**
     * @param  array<string, string>  $fields
     */
    protected function recordViolations(
        ScanResult $result,
        array $fields,
        string $context,
        ?string $titleSnapshot,
        ?string $contentType,
        ?int $contentId,
    ): void {
        foreach ($result->hits as $hit) {
            ForbiddenWordViolation::create([
                'content_type' => $contentType ?? $context,
                'content_id' => $contentId,
                'content_title_snapshot' => $titleSnapshot,
                'field' => $hit['field'] ?? '',
                'matched_word' => $hit['word'] ?? '',
                'category_slug' => $hit['category_slug'] ?? '',
                'action' => $result->action === 'replace' ? 'replace' : 'block',
                'original_excerpt' => mb_substr((string) ($fields[$hit['field'] ?? ''] ?? ''), 0, 500),
                'status' => 'pending',
                'checked_at' => now(),
            ]);
        }
    }
}
