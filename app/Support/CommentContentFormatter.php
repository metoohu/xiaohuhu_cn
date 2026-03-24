<?php

namespace App\Support;

use App\Models\UserSticker;
use Illuminate\Support\Facades\Storage;

class CommentContentFormatter
{
    public const STICKER_PATTERN = '/\[:sticker:(\d+)\]/';

    /**
     * 将评论中的 [:sticker:id] 转为安全 HTML，其余文本转义并换行
     */
    public static function toHtml(string $content): string
    {
        $parts = preg_split(self::STICKER_PATTERN, $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return nl2br(e($content));
        }

        $html = '';
        $i = 0;
        foreach ($parts as $part) {
            if ($i % 2 === 0) {
                $html .= nl2br(e($part));
            } else {
                $id = (int) $part;
                $sticker = UserSticker::find($id);
                if ($sticker && $sticker->image_path) {
                    $url = e(Storage::url($sticker->image_path));
                    $html .= '<img src="'.$url.'" alt="" class="inline-block max-h-10 align-middle rounded mx-0.5" loading="lazy">';
                } else {
                    $html .= e('[:sticker:'.$id.']');
                }
            }
            $i++;
        }

        return $html;
    }

    /**
     * 校验内容中的表情包占位符是否均属于指定用户
     *
     * @return string|null 错误信息，null 表示通过
     */
    public static function validateUserStickers(string $content, int $userId): ?string
    {
        $maxPerComment = (int) config('front.stickers.max_per_comment', 20);
        if (! preg_match_all(self::STICKER_PATTERN, $content, $m)) {
            return null;
        }
        $ids = array_map('intval', $m[1]);
        if (count($ids) > $maxPerComment) {
            return '单条评论最多插入 '.$maxPerComment.' 个自定义表情';
        }
        $unique = array_unique($ids);
        $owned = UserSticker::query()
            ->where('user_id', $userId)
            ->whereIn('id', $unique)
            ->pluck('id')
            ->all();
        if (count($owned) !== count($unique)) {
            return '评论中包含无效或不属于您的表情包';
        }

        return null;
    }
}
