<?php

namespace App\Services\ForbiddenWord\Dto;

/**
 * 单次违禁词命中结果（供 Scanner / JSON 序列化）。
 */
readonly class ForbiddenWordHit
{
    public function __construct(
        public string $field,
        public string $word,
        public string $categorySlug,
        public string $categoryName,
        public string $level,
        public int $start,
        public int $end,
        public string $fieldRole,
        public int $wordId,
        public ?string $replacement = null,
    ) {}

    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'word' => $this->word,
            'category_slug' => $this->categorySlug,
            'category_name' => $this->categoryName,
            'level' => $this->level,
            'start' => $this->start,
            'end' => $this->end,
            'field_role' => $this->fieldRole,
            'word_id' => $this->wordId,
            'suggestion' => $this->replacement,
        ];
    }
}
