<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLoginLog extends Model
{
    protected $table = 'admin_login_logs';

    protected $fillable = ['admin_user_id', 'ip', 'user_agent', 'status', 'email'];

    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 0;

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }
}
