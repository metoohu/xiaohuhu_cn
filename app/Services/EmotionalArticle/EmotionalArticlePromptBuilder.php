<?php

namespace App\Services\EmotionalArticle;

use App\Models\Category;

/**
 * 组装豆包 system/user 提示词：情感向 + 类目语境 + 严格 JSON 输出约定。
 */
final class EmotionalArticlePromptBuilder
{
    public function build(Category $category): array
    {
        $tone = $category->resolvedAiTone();
        $toneGuide = $this->toneGuide($tone);
        $catName = $category->name;
        $catDesc = $category->description ? trim((string) $category->description) : '（无）';

        $system = <<<'SYS'
你是中文情感类专栏作者。请根据用户给出的分类与文风，写一篇原创情感短文。
硬性要求：
1. 内容积极、不渲染自残/暴力/违法；不出现医疗诊断用语；不冒充真人新闻。
2. 正文为 HTML 片段，仅使用常见排版标签：p、h2–h4、ul/ol/li、strong、em、a（href 用 https 或相对路径）、blockquote、table（可选）。禁止 script、iframe、on* 事件、javascript: 链接。
3. 字数约 800–1200 字（以中文为主），分段清晰。
4. 只输出一个 JSON 对象，不要 Markdown 代码围栏，不要多余说明文字。
JSON 字段：title（字符串）、content（HTML 字符串）、seo_title（可空）、seo_keywords（可空）、seo_description（可空，建议 80–160 字纯文本）。
SYS;

        $user = "分类名称：{$catName}\n分类描述：{$catDesc}\n文风：{$toneGuide}\n请在文末用单独一段 <p> 标明：本文为 AI 辅助生成，仅供阅读与情绪陪伴。</p>";

        return [$system, $user];
    }

    private function toneGuide(string $tone): string
    {
        return match ($tone) {
            Category::AI_TONE_HEALING => '治愈：温暖、接纳、轻柔收束，给读者被理解感。',
            Category::AI_TONE_JOURNEY => '人生旅途：成长、选择、时间感，略带叙事。',
            Category::AI_TONE_TRIVIAL => '生活琐碎：小事里的共情与烟火气，语言亲切。',
            Category::AI_TONE_SOBER => '人间清醒：克制、边界、理性温柔，避免说教腔。',
            Category::AI_TONE_QUIET => '宁静角落：慢节奏、留白、低刺激，偏散文感。',
            default => '治愈：温暖、接纳、轻柔收束。',
        };
    }
}
