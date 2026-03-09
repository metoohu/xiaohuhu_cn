<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TinyMCE 富文本编辑器来源
    |--------------------------------------------------------------------------
    |
    | cloud: 使用 Tiny Cloud CDN（需 API 密钥，且需在 Tiny Cloud 后台登记网域）
    | jsdelivr: 使用 jsDelivr CDN（无需密钥，无网域限制）
    |
    */
    'tinymce_source' => env('TINYMCE_SOURCE', 'jsdelivr'),

    /*
    |--------------------------------------------------------------------------
    | TinyMCE Cloud API 密钥（仅当 tinymce_source=cloud 时使用）
    |--------------------------------------------------------------------------
    |
    | 免费注册：https://www.tiny.cloud/auth/signup/
    | 需在 Tiny Cloud 后台添加允许的网域（如 localhost、127.0.0.1）
    |
    */
    'tinymce_api_key' => env('TINYMCE_API_KEY', ''),
];
