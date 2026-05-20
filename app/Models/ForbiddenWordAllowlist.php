<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 违禁词豁免短语（最长匹配优先）。
 */
class ForbiddenWordAllowlist extends Model
{
    protected $table = 'forbidden_word_allowlist';

    protected $fillable = ['phrase', 'is_enabled', 'remark'];

    protected function casts(): array
    {
        return [
            'phrase' => 'encrypted',
            'is_enabled' => 'boolean',
        ];
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }
}
