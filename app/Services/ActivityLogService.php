<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    /**
     * Log an activity
     */
    public function log(
        int $workspaceId,
        ?int $userId,
        string $action,
        Model $subject,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): ActivityLog {
        return ActivityLog::create([
            'workspace_id' => $workspaceId,
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
        ]);
    }

    /**
     * Get activity logs for a workspace
     */
    public function getWorkspaceLogs(int $workspaceId, int $limit = 50)
    {
        return ActivityLog::where('workspace_id', $workspaceId)
            ->with(['user', 'subject'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity logs for a specific subject
     */
    public function getSubjectLogs(Model $subject, int $limit = 50)
    {
        return ActivityLog::where('subject_type', get_class($subject))
            ->where('subject_id', $subject->id)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }
}

