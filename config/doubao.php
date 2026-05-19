<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 豆包（火山方舟）OpenAPI 兼容接口
    |--------------------------------------------------------------------------
    | 用于定时生成情感类文章。关闭 DOUBAO_ENABLED 时调度命令会跳过，避免误调用。
    */
    'enabled' => (bool) env('DOUBAO_ENABLED', false),

    'api_key' => env('DOUBAO_API_KEY'),

    // 不含末尾斜杠，例如 https://ark.cn-beijing.volces.com/api/v3
    'api_base' => rtrim((string) env('DOUBAO_API_BASE', 'https://ark.cn-beijing.volces.com/api/v3'), '/'),

    // 方舟推理接入点 ID（ep-xxxx）
    'model' => env('DOUBAO_MODEL'),

    'timeout' => (int) env('DOUBAO_TIMEOUT', 120),
];
