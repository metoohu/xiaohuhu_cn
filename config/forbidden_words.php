<?php

/**
 * 违禁词模块配置：缓存、字段角色映射、各场景文案。
 */
return [

    'cache_key' => 'forbidden_words:dict',

    'cache_ttl' => (int) env('FORBIDDEN_WORDS_CACHE_TTL', 3600),

    'scan_throttle' => 60,

    'messages' => [
        'redline' => '含违规敏感内容，请删除修改后重试',
        'tone_title' => '含极端负面词汇，需调整为治愈温和表述',
        'tone_replace' => '正文含调性敏感词，已建议替换表述',
        'tone_body' => '正文中调性敏感词过多，请修改后再提交',
    ],

    // 物理字段名 => 角色：title | body | other
    'field_roles' => [
        'title' => ['title'],
        'body' => ['content', 'content_public', 'content_pending'],
        'other' => ['excerpt', 'seo_title', 'seo_keywords', 'seo_description', 'signature', 'mood_text', 'interests', 'occupation', 'tags'],
    ],

    'contexts' => [
        'article',
        'user_article',
        'comment',
        'user_profile',
        'import_row',
    ],

];
