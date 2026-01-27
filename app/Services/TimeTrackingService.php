<?php

namespace App\Services;

use App\Models\TimeEntry;
use App\Models\Task;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class TimeTrackingService
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {}

    /**
     * Start a timer for a task
     */
    public function startTimer(Task $task, User $user, ?string $description = null): TimeEntry
    {
        return DB::transaction(function () use ($task, $user, $description) {
            // Stop any existing active timer for this user
            $activeTimer = $user->getActiveTimer();
            if ($activeTimer) {
                $this->stopTimer($activeTimer, $user);
            }

            // Create new time entry
            $timeEntry = TimeEntry::create([
                'workspace_id' => $task->workspace_id,
                'task_id' => $task->id,
                'user_id' => $user->id,
                'start_time' => now(),
                'end_time' => null,
                'duration' => null,
                'description' => $description,
                'is_manual' => false,
            ]);

            // Log activity
            $this->activityLogService->log(
                $task->workspace_id,
                $user->id,
                'time_started',
                $task,
                null,
                ['time_entry_id' => $timeEntry->id]
            );

            return $timeEntry;
        });
    }

    /**
     * Stop a timer
     */
    public function stopTimer(TimeEntry $timeEntry, User $user): TimeEntry
    {
        return DB::transaction(function () use ($timeEntry, $user) {
            if ($timeEntry->end_time) {
                throw new \Exception('Timer is already stopped.');
            }

            $timeEntry->stop();

            // Log activity
            $this->activityLogService->log(
                $timeEntry->workspace_id,
                $user->id,
                'time_stopped',
                $timeEntry->task,
                null,
                ['duration' => $timeEntry->duration]
            );

            return $timeEntry->fresh();
        });
    }

    /**
     * Create manual time entry
     */
    public function createManualEntry(
        Task $task,
        User $user,
        \DateTime $startTime,
        \DateTime $endTime,
        ?string $description = null,
        bool $isBillable = false
    ): TimeEntry {
        return DB::transaction(function () use ($task, $user, $startTime, $endTime, $description, $isBillable) {
            // Validate time range
            if ($endTime <= $startTime) {
                throw new \Exception('End time must be after start time.');
            }

            // Check for overlapping entries (optional - can be configurable)
            $overlapping = TimeEntry::where('user_id', $user->id)
                ->where('task_id', $task->id)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })
                ->exists();

            if ($overlapping) {
                throw new \Exception('Time entry overlaps with existing entry.');
            }

            // Calculate duration in seconds
            $duration = abs($endTime->getTimestamp() - $startTime->getTimestamp());

            $timeEntry = TimeEntry::create([
                'workspace_id' => $task->workspace_id,
                'task_id' => $task->id,
                'user_id' => $user->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration' => $duration,
                'description' => $description,
                'is_billable' => $isBillable,
                'is_manual' => true,
            ]);

            // Log activity
            $this->activityLogService->log(
                $task->workspace_id,
                $user->id,
                'time_logged',
                $task,
                null,
                ['time_entry_id' => $timeEntry->id, 'duration' => $duration]
            );

            return $timeEntry;
        });
    }

    /**
     * Update time entry
     */
    public function updateEntry(TimeEntry $timeEntry, array $data, User $user): TimeEntry
    {
        return DB::transaction(function () use ($timeEntry, $data, $user) {
            // Only allow editing own entries (guests and members can edit their own)
            // Admins can edit any entry
            $role = $user->getRoleInWorkspace($timeEntry->workspace_id);
            if ($timeEntry->user_id !== $user->id && !in_array($role, ['owner', 'admin'])) {
                throw new \Exception('You can only edit your own time entries.');
            }

            // Recalculate duration if times changed
            if (isset($data['start_time']) || isset($data['end_time'])) {
                $startTime = $data['start_time'] ?? $timeEntry->start_time;
                $endTime = $data['end_time'] ?? $timeEntry->end_time;
                
                // Convert to timestamps for comparison and calculation
                // Handle Carbon instances (Laravel casts datetime to Carbon)
                $startTimestamp = $startTime instanceof \Carbon\Carbon 
                    ? $startTime->timestamp 
                    : ($startTime instanceof \DateTime 
                        ? $startTime->getTimestamp() 
                        : (new \DateTime($startTime))->getTimestamp());
                
                $endTimestamp = $endTime instanceof \Carbon\Carbon 
                    ? $endTime->timestamp 
                    : ($endTime instanceof \DateTime 
                        ? $endTime->getTimestamp() 
                        : (new \DateTime($endTime))->getTimestamp());
                
                if ($endTimestamp <= $startTimestamp) {
                    throw new \Exception('End time must be after start time.');
                }

                // Calculate duration in seconds using timestamps
                $data['duration'] = abs($endTimestamp - $startTimestamp);
            }

            $timeEntry->update($data);

            return $timeEntry->fresh();
        });
    }

    /**
     * Delete time entry
     */
    public function deleteEntry(TimeEntry $timeEntry, User $user): bool
    {
        $role = $user->getRoleInWorkspace($timeEntry->workspace_id);
        if ($timeEntry->user_id !== $user->id && !in_array($role, ['owner', 'admin'])) {
            throw new \Exception('You can only delete your own time entries.');
        }

        return $timeEntry->delete();
    }

    /**
     * Get time summary for a task
     */
    public function getTaskTimeSummary(Task $task): array
    {
        $entries = $task->timeEntries()->whereNotNull('end_time')->get();
        
        $totalSeconds = $entries->sum('duration') ?? 0;
        $billableSeconds = $entries->where('is_billable', true)->sum('duration') ?? 0;
        
        return [
            'total_seconds' => $totalSeconds,
            'total_formatted' => $this->formatDuration($totalSeconds),
            'billable_seconds' => $billableSeconds,
            'billable_formatted' => $this->formatDuration($billableSeconds),
            'entries_count' => $entries->count(),
            'estimated_minutes' => $task->estimated_time,
            'estimated_formatted' => $task->estimated_time ? $this->formatDuration($task->estimated_time * 60) : null,
        ];
    }

    /**
     * Get time summary for a user in a workspace
     */
    public function getUserTimeSummary(User $user, int $workspaceId, ?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $query = TimeEntry::where('user_id', $user->id)
            ->where('workspace_id', $workspaceId)
            ->whereNotNull('end_time');

        if ($startDate) {
            $query->where('start_time', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('start_time', '<=', $endDate);
        }

        $entries = $query->get();
        
        $totalSeconds = $entries->sum('duration') ?? 0;
        $billableSeconds = $entries->where('is_billable', true)->sum('duration') ?? 0;
        
        // Group by project
        $byProject = $entries->groupBy('task.project_id')->map(function ($projectEntries) {
            return [
                'total_seconds' => $projectEntries->sum('duration'),
                'entries_count' => $projectEntries->count(),
            ];
        });

        return [
            'total_seconds' => $totalSeconds,
            'total_formatted' => $this->formatDuration($totalSeconds),
            'billable_seconds' => $billableSeconds,
            'billable_formatted' => $this->formatDuration($billableSeconds),
            'entries_count' => $entries->count(),
            'by_project' => $byProject,
        ];
    }

    /**
     * Format duration in seconds to human-readable string
     */
    protected function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }

    /**
     * Auto-stop timers that have been running too long
     */
    public function autoStopLongRunningTimers(int $maxHours = 24): void
    {
        $cutoffTime = now()->subHours($maxHours);
        
        TimeEntry::whereNull('end_time')
            ->where('start_time', '<', $cutoffTime)
            ->get()
            ->each(function ($entry) {
                $entry->stop();
                // Optionally notify user
                // NotificationService::notifyTimerAutoStopped($entry);
            });
    }
}

