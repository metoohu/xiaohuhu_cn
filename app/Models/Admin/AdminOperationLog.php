<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminOperationLog extends Model
{
    protected $table = 'admin_operation_logs';

    protected $fillable = ['admin_user_id', 'action', 'module', 'ip', 'user_agent', 'extra'];

    protected $casts = ['extra' => 'array'];

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    public static function log(string $action, ?string $module = null, ?array $extra = null): void
    {
        static::create([
            'admin_user_id' => auth()->guard('admin')->id(),
            'action' => $action,
            'module' => $module,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'extra' => $extra,
        ]);
    }
}
