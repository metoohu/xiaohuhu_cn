<?php

namespace App\Observers;

use App\Models\ForbiddenWord;
use App\Services\ForbiddenWord\ForbiddenWordDictionaryBuilder;

/**
 * 词条变更后清除词典缓存。
 */
class ForbiddenWordObserver
{
    public function saved(ForbiddenWord $word): void
    {
        app(ForbiddenWordDictionaryBuilder::class)->forgetCache();
    }

    public function deleted(ForbiddenWord $word): void
    {
        app(ForbiddenWordDictionaryBuilder::class)->forgetCache();
    }
}
