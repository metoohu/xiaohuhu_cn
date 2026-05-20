<?php

namespace App\Observers;

use App\Models\ForbiddenWordAllowlist;
use App\Services\ForbiddenWord\ForbiddenWordDictionaryBuilder;

/**
 * 豁免短语变更后清除词典缓存。
 */
class ForbiddenWordAllowlistObserver
{
    public function saved(ForbiddenWordAllowlist $row): void
    {
        app(ForbiddenWordDictionaryBuilder::class)->forgetCache();
    }

    public function deleted(ForbiddenWordAllowlist $row): void
    {
        app(ForbiddenWordDictionaryBuilder::class)->forgetCache();
    }
}
