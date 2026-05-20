<?php

namespace App\Services\ForbiddenWord;

/**
 * 扫描前文本归一化：去 HTML、全角空格、合并空白、ASCII 小写。
 */
class TextNormalizer
{
    public function normalize(string $input): string
    {
        $text = $input;

        if (str_contains($text, '<')) {
            $text = strip_tags($text);
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (function_exists('mb_convert_kana')) {
            $text = mb_convert_kana($text, 's', 'UTF-8');
        }

        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text);

        return $this->lowerAscii($text);
    }

    private function lowerAscii(string $text): string
    {
        return preg_replace_callback('/[A-Z]+/u', static fn (array $m) => strtolower($m[0]), $text) ?? $text;
    }
}
