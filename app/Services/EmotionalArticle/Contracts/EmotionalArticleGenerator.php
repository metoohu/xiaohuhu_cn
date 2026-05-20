<?php

namespace App\Services\EmotionalArticle\Contracts;

use App\Models\Category;

/**
 * AI 情感文生成器契约（便于 Job 测试注入 Fake）。
 */
interface EmotionalArticleGenerator
{
    /**
     * @return array{title: string, content: string, seo_title: ?string, seo_keywords: ?string, seo_description: ?string}
     */
    public function generate(Category $category): array;
}
