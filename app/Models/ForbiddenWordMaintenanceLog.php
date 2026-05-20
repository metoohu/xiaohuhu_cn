<?php

namespace App\Models;

use App\Models\Admin\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 词库维护操作日志（仅 created_at）。
 */
class ForbiddenWordMaintenanceLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_user_id',
        'action',
        'subject_type',
        'subject_id',
        'payload',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }
}
