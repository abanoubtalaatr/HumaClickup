<?php

namespace App\Services;

use App\Models\DailyProgress;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;

/**
 * DailyProgressService
 * 
 * Core service for calculating and managing per-guest, per-day progress.
 * 
 * Business Rules:
 * - Each guest MUST complete 1 main task per day
 * - Each main task MUST be >= 6 hours
 * - Progress = (completed_hours / 6) × 100, capped at 100%
 * - Only main tasks count (bugs don't count)
 * - Progress requires mentor approval to lock
 */
class DailyProgressService
{
    /**
     * Calculate daily progress for a guest on a specific date.
     * 
     * @param User $guest
     * @param Project $project
     * @param Carbon $date
     * @return DailyProgress
     */
    public function calculateDailyProgress(User $guest, Project $project, Carbon $date): DailyProgress
    {
        // Find or create daily progress record
        $progress = DailyProgress::firstOrCreate(
            [
                'workspace_id' => $project->workspace_id,
                'project_id' => $project->id,
                'user_id' => $guest->id,
                'date' => $date,
            ],
            [
                'required_hours' => 6.00,
                'completed_hours' => 0,
                'progress_percentage' => 0,
            ]
        );

        // Don't recalculate if already approved (locked)
        if ($progress->isApproved()) {
            return $progress;
        }

        // Find main task for this guest on this date
        $mainTask = $this->findMainTaskForDay($guest, $project, $date);

        if (!$mainTask) {
            // No main task = 0 progress
            $progress->update([
                'task_id' => null,
                'completed_hours' => 0,
                'progress_percentage' => 0,
            ]);
            return $progress;
        }

        // Check if task is complete
        $isComplete = $this->isTaskComplete($mainTask);

        // Calculate completed hours
        // Rule: Only count hours if task is DONE
        $completedHours = $isComplete ? $mainTask->estimated_time : 0;

        // Calculate progress percentage
        $progressPercentage = ($completedHours / $progress->required_hours) * 100;
        $progressPercentage = min($progressPercentage, 100); // Cap at 100%

        // Update progress
        $progress->update([
            'task_id' => $mainTask->id,
            'completed_hours' => $completedHours,
            'progress_percentage' => $progressPercentage,
        ]);

        return $progress;
    }

    /**
     * Find the main task for a guest on a specific day.
     * 
     * Rules:
     * - Task must be assigned to the guest
     * - Task must be a main task (is_main_task = 'yes')
     * - Task must be for the given date (assigned_date or due_date)
     * 
     * @param User $guest
     * @param Project $project
     * @param Carbon $date
     * @return Task|null
     */
    public function findMainTaskForDay(User $guest, Project $project, Carbon $date): ?Task
    {
        return Task::where('project_id', $project->id)
            ->where('is_main_task', 'yes')
            ->whereHas('assignees', fn($q) => $q->where('users.id', $guest->id))
            ->where(function ($q) use ($date) {
                $q->whereDate('assigned_date', $date)
                  ->orWhereDate('due_date', $date);
            })
            ->first();
    }

    /**
     * Check if a task is complete.
     * 
     * @param Task $task
     * @return bool
     */
    public function isTaskComplete(Task $task): bool
    {
        return $task->status && $task->status->type === 'done';
    }

    /**
     * Get daily progress for a date range.
     * 
     * @param User $guest
     * @param Project $project
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDailyProgressRange(User $guest, Project $project, Carbon $startDate, Carbon $endDate)
    {
        return DailyProgress::where('user_id', $guest->id)
            ->where('project_id', $project->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }

    /**
     * Calculate weekly progress summary.
     * 
     * @param User $guest
     * @param Project $project
     * @param Carbon $weekStart
     * @return array
     */
    public function calculateWeeklyProgress(User $guest, Project $project, Carbon $weekStart): array
    {
        $weekEnd = $weekStart->copy()->endOfWeek();

        $dailyProgress = $this->getDailyProgressRange($guest, $project, $weekStart, $weekEnd);

        // Exclude weekend days (Friday & Saturday)
        $workingDays = $dailyProgress->filter(function ($progress) {
            $dayOfWeek = $progress->date->dayOfWeek;
            return !in_array($dayOfWeek, [5, 6]); // 5 = Friday, 6 = Saturday
        });

        $totalHours = $workingDays->sum('completed_hours');
        $averageProgress = $workingDays->avg('progress_percentage') ?? 0;
        $meetsTarget = $totalHours >= 30; // 30 hours per week

        return [
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d'),
            'working_days_count' => $workingDays->count(),
            'total_hours' => round($totalHours, 2),
            'average_progress' => round($averageProgress, 2),
            'meets_weekly_target' => $meetsTarget,
            'daily_breakdown' => $workingDays->map(function ($progress) {
                return [
                    'date' => $progress->date->format('Y-m-d'),
                    'day_name' => $progress->date->format('l'),
                    'completed_hours' => $progress->completed_hours,
                    'progress_percentage' => $progress->progress_percentage,
                    'approved' => $progress->approved,
                ];
            })->values()->all(),
        ];
    }

    /**
     * Get guests who don't have main tasks for today.
     * 
     * @param Project $project
     * @param Carbon $date
     * @return \Illuminate\Support\Collection
     */
    public function getGuestsWithoutMainTask(Project $project, Carbon $date)
    {
        $guestsWithTasks = Task::where('project_id', $project->id)
            ->where('is_main_task', 'yes')
            ->where(function ($q) use ($date) {
                $q->whereDate('assigned_date', $date)
                  ->orWhereDate('due_date', $date);
            })
            ->with('assignees')
            ->get()
            ->pluck('assignees')
            ->flatten()
            ->pluck('id')
            ->unique();

        return $project->getGuestMembers()->filter(function ($guest) use ($guestsWithTasks) {
            return !$guestsWithTasks->contains($guest->id);
        });
    }

    /**
     * Get guests with incomplete progress for today.
     * 
     * @param Project $project
     * @param Carbon $date
     * @return \Illuminate\Support\Collection
     */
    public function getGuestsWithIncompleteProgress(Project $project, Carbon $date)
    {
        $incompleteProgress = DailyProgress::where('project_id', $project->id)
            ->where('date', $date)
            ->where('progress_percentage', '<', 100)
            ->get();

        return $incompleteProgress->map(fn($p) => $p->user);
    }

    /**
     * Get pending approvals for a project.
     * 
     * @param Project $project
     * @param Carbon|null $date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingApprovals(Project $project, ?Carbon $date = null)
    {
        $query = DailyProgress::where('project_id', $project->id)
            ->where('approved', false)
            ->with(['user', 'task']);

        if ($date) {
            $query->where('date', $date);
        }

        return $query->orderBy('date', 'desc')->get();
    }
}
