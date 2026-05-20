<?php

namespace App\Services\ForbiddenWord;

use App\Services\ForbiddenWord\Dto\ForbiddenWordDecision;
use App\Services\ForbiddenWord\Dto\ForbiddenWordHit;

/**
 * 红线 / 调性分级决策（标题拦截、正文 1～2 处替换、≥3 处拦截）。
 */
class ForbiddenWordPolicy
{
    /**
     * @param  array<string, list<ForbiddenWordHit>>  $hitsByField
     * @param  array<string, string>  $originalFields
     */
    public function decide(array $hitsByField, array $originalFields): ForbiddenWordDecision
    {
        $allHits = [];
        foreach ($hitsByField as $hits) {
            foreach ($hits as $hit) {
                $allHits[] = $hit;
            }
        }

        if ($allHits === []) {
            return new ForbiddenWordDecision(allowed: true, action: null);
        }

        $messages = config('forbidden_words.messages', []);

        foreach ($allHits as $hit) {
            if ($hit->level === 'block') {
                return new ForbiddenWordDecision(
                    allowed: false,
                    action: 'block',
                    messages: [$messages['redline'] ?? '含违规敏感内容，请删除修改后重试'],
                );
            }
        }

        foreach ($allHits as $hit) {
            if ($hit->fieldRole === 'title' && $hit->level === 'tone') {
                return new ForbiddenWordDecision(
                    allowed: false,
                    action: 'block',
                    messages: [$messages['tone_title'] ?? '含极端负面词汇，需调整为治愈温和表述'],
                );
            }
        }

        $toneWordIds = [];
        foreach ($allHits as $hit) {
            if (in_array($hit->fieldRole, ['body', 'other'], true) && $hit->level === 'tone') {
                $toneWordIds[$hit->wordId] = true;
            }
        }

        $distinctTone = count($toneWordIds);

        if ($distinctTone >= 3) {
            return new ForbiddenWordDecision(
                allowed: false,
                action: 'block',
                messages: [$messages['tone_body'] ?? '正文中调性敏感词过多，请修改后再提交'],
            );
        }

        if ($distinctTone >= 1) {
            $replaced = $this->applyReplacements($originalFields, $allHits);
            if ($replaced === null) {
                return new ForbiddenWordDecision(
                    allowed: false,
                    action: 'block',
                    messages: [$messages['tone_body'] ?? '正文中调性敏感词过多，请修改后再提交'],
                );
            }

            return new ForbiddenWordDecision(
                allowed: true,
                action: 'replace',
                replacedFields: $replaced,
                messages: [$messages['tone_replace'] ?? '正文含调性敏感词，已建议替换表述'],
            );
        }

        return new ForbiddenWordDecision(allowed: true, action: null);
    }

    /**
     * @param  list<ForbiddenWordHit>  $hits
     * @return array<string, string>|null  null 表示缺少 replacement 无法替换
     */
    protected function applyReplacements(array $originalFields, array $hits): ?array
    {
        $replaced = $originalFields;

        foreach ($hits as $hit) {
            if ($hit->level !== 'tone' || ! in_array($hit->fieldRole, ['body', 'other'], true)) {
                continue;
            }
            if (empty($hit->replacement)) {
                return null;
            }
            $field = $hit->field;
            if (! isset($replaced[$field])) {
                continue;
            }
            $replaced[$field] = str_replace($hit->word, $hit->replacement, $replaced[$field]);
        }

        $changed = [];
        foreach ($replaced as $key => $value) {
            if (($originalFields[$key] ?? '') !== $value) {
                $changed[$key] = $value;
            }
        }

        return $changed === [] ? $originalFields : $changed;
    }
}
