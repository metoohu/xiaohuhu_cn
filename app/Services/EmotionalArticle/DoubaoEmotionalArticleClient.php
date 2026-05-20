<?php

namespace App\Services\EmotionalArticle;

use App\Services\EmotionalArticle\Contracts\EmotionalArticleGenerator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * 调用火山方舟（豆包）OpenAPI 兼容 chat/completions，解析 JSON 稿件。
 */
final class DoubaoEmotionalArticleClient implements EmotionalArticleGenerator
{
    public function __construct(
        private EmotionalArticlePromptBuilder $promptBuilder,
    ) {}

    /**
     * @return array{title: string, content: string, seo_title: ?string, seo_keywords: ?string, seo_description: ?string}
     */
    public function generate(\App\Models\Category $category): array
    {
        if (! config('doubao.enabled')) {
            throw new RuntimeException('豆包接口未启用（DOUBAO_ENABLED=false）');
        }
        $apiKey = config('doubao.api_key');
        $model = config('doubao.model');
        if (! is_string($apiKey) || $apiKey === '' || ! is_string($model) || $model === '') {
            throw new RuntimeException('请配置 DOUBAO_API_KEY 与 DOUBAO_MODEL（方舟接入点 ID）');
        }

        $base = config('doubao.api_base');
        $url = $base.'/chat/completions';
        [$system, $user] = $this->promptBuilder->build($category);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout((int) config('doubao.timeout', 120))
            ->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'temperature' => 0.75,
            ]);

        if (! $response->successful()) {
            Log::warning('doubao.chat.error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException('豆包接口请求失败：HTTP '.$response->status());
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($content) || $content === '') {
            throw new RuntimeException('豆包返回内容为空');
        }

        $json = $this->extractJsonObject($content);
        $data = json_decode($json, true);
        if (! is_array($data)) {
            throw new RuntimeException('豆包返回非合法 JSON');
        }

        $title = isset($data['title']) ? trim((string) $data['title']) : '';
        $html = isset($data['content']) ? trim((string) $data['content']) : '';
        if ($title === '' || $html === '') {
            throw new RuntimeException('JSON 缺少 title 或 content');
        }

        $sanitizer = new EmotionalArticleHtmlSanitizer;
        $html = $sanitizer->sanitize($html);

        return [
            'title' => $title,
            'content' => $html,
            'seo_title' => isset($data['seo_title']) ? $this->nullableString($data['seo_title']) : null,
            'seo_keywords' => isset($data['seo_keywords']) ? $this->nullableString($data['seo_keywords']) : null,
            'seo_description' => isset($data['seo_description']) ? $this->nullableString($data['seo_description']) : null,
        ];
    }

    private function nullableString(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }

    /**
     * 兼容模型偶发包裹 ```json ... ``` 的情况。
     */
    private function extractJsonObject(string $raw): string
    {
        $raw = trim($raw);
        if (str_starts_with($raw, '```')) {
            $raw = preg_replace('/^```[a-zA-Z]*\s*/', '', $raw) ?? $raw;
            $raw = preg_replace('/\s*```$/', '', $raw) ?? $raw;
        }

        return trim($raw);
    }
}
