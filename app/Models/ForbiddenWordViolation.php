<?php

namespace App\Models;

use App\Models\Admin\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 违禁词违规审计记录（禁止物理删除）。
 */
class ForbiddenWordViolation extends Model
{
    protected $fillable = [
        'content_type',
        'content_id',
        'content_title_snapshot',
        'field',
        'matched_word',
        'category_slug',
        'action',
        'original_excerpt',
        'replaced_excerpt',
        'handler_admin_id',
        'status',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function () {
            throw new \RuntimeException('违规记录不可删除');
        });
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'handler_admin_id');
    }
}
