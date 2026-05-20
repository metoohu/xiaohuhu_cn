<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 违禁词条目（word / replacement 加密存储）。
 */
class ForbiddenWord extends Model
{
    protected $fillable = [
        'category_id',
        'word',
        'match_type',
        'replacement',
        'is_enabled',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'word' => 'encrypted',
            'replacement' => 'encrypted',
            'is_enabled' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForbiddenWordCategory::class, 'category_id');
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }
}
