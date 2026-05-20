<?php

namespace App\Services\ForbiddenWord;

use App\Models\ForbiddenWordMaintenanceLog;
use Illuminate\Support\Facades\Auth;

/**
 * 词库维护操作审计：写入 forbidden_word_maintenance_logs。
 */
class ForbiddenWordMaintenanceLogger
{
    /**
     * 记录一次维护操作。
     *
     * @param  array<string, mixed>  $payload
     */
    public function log(string $action, string $subjectType, ?int $subjectId, array $payload = []): ForbiddenWordMaintenanceLog
    {
        return ForbiddenWordMaintenanceLog::query()->create([
            'admin_user_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
