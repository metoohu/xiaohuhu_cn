<?php

namespace App\Services\EmotionalArticle;

/**
 * 将模型返回的正文限制在后台 TinyMCE 常用安全标签内。
 */
final class EmotionalArticleHtmlSanitizer
{
    /**
     * @var string 允许的标签（与 TinyMCE 插件 lists/link/image/code/table 等常见输出对齐）
     */
    private const ALLOWED_TAGS = '<p><br><h2><h3><h4><ul><ol><li><strong><em><b><i><u><a><blockquote><div><span><table><thead><tbody><tr><th><td><img><hr>';

    public function sanitize(string $html): string
    {
        $html = strip_tags($html, self::ALLOWED_TAGS);

        return trim($html);
    }
}
