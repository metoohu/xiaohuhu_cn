<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 首页展示文章数量
    |--------------------------------------------------------------------------
    */
    'home_article_count' => env('FRONT_HOME_ARTICLE_COUNT', 10),

    /*
    |--------------------------------------------------------------------------
    | 列表页分页条数
    |--------------------------------------------------------------------------
    */
    'article_per_page' => env('FRONT_ARTICLE_PER_PAGE', 15),

    /*
    |--------------------------------------------------------------------------
    | 数据缓存时间（分钟）
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => env('FRONT_CACHE_TTL', 30),

    /*
    |--------------------------------------------------------------------------
    | SEO 基础配置
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'keywords' => env('FRONT_SEO_KEYWORDS', '小糊涂人生馆, 人间清醒, 治愈文字, 生活感悟'),
        'description' => env('FRONT_SEO_DESCRIPTION', '人间清醒，治愈文字。在喧嚣中寻一方宁静，用文字温暖你我。'),
    ],

    /*
    |--------------------------------------------------------------------------
    | 用户自定义表情包（评论 [:sticker:id]）
    |--------------------------------------------------------------------------
    */
    'stickers' => [
        'upload_path' => env('FRONT_STICKER_UPLOAD_PATH', 'uploads/stickers'),
        'max_per_user' => (int) env('FRONT_STICKER_MAX_PER_USER', 50),
        'max_per_comment' => (int) env('FRONT_STICKER_MAX_PER_COMMENT', 20),
        'max_kb' => (int) env('FRONT_STICKER_MAX_KB', 512),
    ],

    /*
    |--------------------------------------------------------------------------
    | 用户社区稿（投稿规则，与后台分类 user_can_submit 配合）
    |--------------------------------------------------------------------------
    */
    'user_article' => [
        'daily_submit_limit' => (int) env('FRONT_USER_ARTICLE_DAILY_LIMIT', 2),
        'title_min' => (int) env('FRONT_USER_ARTICLE_TITLE_MIN', 4),
        'title_max' => (int) env('FRONT_USER_ARTICLE_TITLE_MAX', 90),
        'content_min' => (int) env('FRONT_USER_ARTICLE_CONTENT_MIN', 20),
        'content_max' => (int) env('FRONT_USER_ARTICLE_CONTENT_MAX', 20000),
        'max_tags' => (int) env('FRONT_USER_ARTICLE_MAX_TAGS', 3),
    ],

];
