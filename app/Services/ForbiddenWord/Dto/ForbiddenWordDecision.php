<?php

namespace App\Services\ForbiddenWord\Dto;

/**
 * Policy 决策输出：是否拦截、替换字段、提示文案。
 */
readonly class ForbiddenWordDecision
{
    /**
     * @param  array<string, string>  $replacedFields
     * @param  list<string>  $messages
     */
    public function __construct(
        public bool $allowed,
        public ?string $action,
        public array $replacedFields = [],
        public array $messages = [],
    ) {}
}
