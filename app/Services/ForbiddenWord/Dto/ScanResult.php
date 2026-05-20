<?php

namespace App\Services\ForbiddenWord\Dto;

/**
 * 扫描 API / 强校验统一返回结构。
 */
readonly class ScanResult
{
    /**
     * @param  list<array<string, mixed>>  $hits
     * @param  array<string, string>  $replacedFields
     * @param  list<string>  $messages
     */
    public function __construct(
        public bool $allowed,
        public ?string $action,
        public array $hits,
        public array $replacedFields = [],
        public array $messages = [],
    ) {}

    public function toArray(): array
    {
        return [
            'allowed' => $this->allowed,
            'action' => $this->action,
            'hits' => $this->hits,
            'replaced_fields' => $this->replacedFields,
            'messages' => $this->messages,
        ];
    }
}
