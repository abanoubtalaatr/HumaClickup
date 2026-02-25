<?php

namespace App\Services;

use App\Models\DailyProgress;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;

class ProgressTrackingService
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Update daily progress when a task is completed.
     */
    public function updateDailyProgress(User $user, Task $task, Carbon $date = null): DailyProgress
    {
        $date = $date ?? today();
        $taskHours = $task->getCompletionHours();

        // Find or create daily progress record
        $progress = DailyProgress::firstOrCreate(
            [
                'workspace_id' => $task->workspace_id,
                'project_id' => $task->project_id,
                'user_id' => $user->id,
                'date' => $date,
            ],
            [
                'completed_tasks_count' => 0,
                'total_hours' => 0,
                'progress_percentage' => 0,
            ]
        );

        // Update progress
        $progress->incrementCompletedTasks($taskHours);

        // Update user's weekly hours
        $user->updateWeeklyHours($taskHours);

        // Auto-mark attendance if task meets minimum hours
        if ($taskHours >= 6) {
            $this->attendanceService->autoMarkAttendance($user, $task->project, $taskHours, $date);
        }

        return $progress;
    }

    /**
     * Get daily progress for a user.
     */
    public function getDailyProgress(User $user, Project $project, Carbon $date): ?DailyProgress
    {
        return DailyProgress::where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->where('date', $date)
            ->first();
    }

    /**
     * Get weekly progress summary for a user.
     */
    public function getWeeklyProgressSummary(User $user, Project $project, Carbon $weekStart = null): array
    {
        $weekStart = $weekStart ?? now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $weeklyProgress = DailyProgress::where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        $dailyBreakdown = [];
        for ($date = $weekStart->copy(); $date->lte($weekEnd); $date->addDay()) {
            $dayProgress = $weeklyProgress->where('date', $date->format('Y-m-d'))->first();
            
            $dailyBreakdown[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'completed_tasks' => $dayProgress ? $dayProgress->completed_tasks_count : 0,
                'hours' => $dayProgress ? $dayProgress->total_hours : 0,
                'progress_percentage' => $dayProgress ? $dayProgress->progress_percentage : 0,
            ];
        }

        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'project_id' => $project->id,
            'project_name' => $project->name,
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d'),
            'total_tasks_completed' => $weeklyProgress->sum('completed_tasks_count'),
            'total_hours' => $weeklyProgress->sum('total_hours'),
            'average_daily_hours' => $weeklyProgress->avg('total_hours'),
            'target_hours' => $project->weekly_hours_target ?? 30,
            'meets_target' => $user->meets_weekly_target,
            'daily_breakdown' => $dailyBreakdown,
        ];
    }

    /**
     * Get project progress overview.
     */
    public function getProjectProgressOverview(Project $project, Carbon $date = null): array
    {
        $date = $date ?? today();

        $teamMembers = $project->getTeamMembers();
        $memberProgress = [];

        foreach ($teamMembers as $member) {
            $progress = $this->getDailyProgress($member, $project, $date);
            
            $memberProgress[] = [
                'user_id' => $member->id,
                'user_name' => $member->name,
                'completed_tasks' => $progress ? $progress->completed_tasks_count : 0,
                'hours' => $progress ? $progress->total_hours : 0,
                'progress_percentage' => $progress ? $progress->progress_percentage : 0,
                'met_daily_target' => $progress ? $progress->metDailyTarget() : false,
            ];
        }

        return [
            'project_name' => $project->name,
            'date' => $date->format('Y-m-d'),
            'total_team_members' => $teamMembers->count(),
            'members_met_target' => collect($memberProgress)->where('met_daily_target', true)->count(),
            'total_tasks_completed' => collect($memberProgress)->sum('completed_tasks'),
            'total_hours' => collect($memberProgress)->sum('hours'),
            'member_progress' => $memberProgress,
        ];
    }

    /**
     * Get users not meeting daily target.
     */
    public function getUsersNotMeetingDailyTarget(Project $project, Carbon $date = null): array
    {
        $date = $date ?? today();
        $overview = $this->getProjectProgressOverview($project, $date);

        return array_filter($overview['member_progress'], function ($member) {
            return !$member['met_daily_target'];
        });
    }

    /**
     * Get monthly progress report.
     */
    public function getMonthlyProgressReport(User $user, Project $project, int $month = null, int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $monthlyProgress = DailyProgress::where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $workingDays = $monthlyProgress->count();
        $daysMetTarget = $monthlyProgress->filter(fn($p) => $p->metDailyTarget())->count();

        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'project_name' => $project->name,
            'month' => $month,
            'year' => $year,
            'working_days' => $workingDays,
            'days_met_target' => $daysMetTarget,
            'total_tasks_completed' => $monthlyProgress->sum('completed_tasks_count'),
            'total_hours' => $monthlyProgress->sum('total_hours'),
            'average_daily_hours' => $monthlyProgress->avg('total_hours'),
            'consistency_rate' => $workingDays > 0 ? ($daysMetTarget / $workingDays) * 100 : 0,
            'daily_details' => $monthlyProgress->map(function ($progress) {
                return [
                    'date' => $progress->date->format('Y-m-d'),
                    'tasks' => $progress->completed_tasks_count,
                    'hours' => $progress->total_hours,
                    'percentage' => $progress->progress_percentage,
                ];
            }),
        ];
    }

    /**
     * Calculate overall project completion percentage.
     */
    public function calculateProjectCompletion(Project $project): float
    {
        $totalMainTasks = $project->tasks()->where('is_main_task', 'yes')->count();
        
        if ($totalMainTasks === 0) {
            return 0;
        }

        $completedMainTasks = $project->tasks()
            ->where('is_main_task', 'yes')
            ->whereHas('status', fn($q) => $q->where('type', 'done'))
            ->count();

        return ($completedMainTasks / $totalMainTasks) * 100;
    }

    /**
     * Get team performance ranking.
     */
    public function getTeamPerformanceRanking(Project $project, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $teamMembers = $project->getTeamMembers();
        $rankings = [];

        foreach ($teamMembers as $member) {
            $progress = DailyProgress::where('user_id', $member->id)
                ->where('project_id', $project->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $rankings[] = [
                'user_id' => $member->id,
                'user_name' => $member->name,
                'total_tasks' => $progress->sum('completed_tasks_count'),
                'total_hours' => $progress->sum('total_hours'),
                'average_hours' => $progress->avg('total_hours'),
                'working_days' => $progress->count(),
                'score' => $progress->sum('completed_tasks_count') * 10 + $progress->sum('total_hours'),
            ];
        }

        // Sort by score descending
        usort($rankings, fn($a, $b) => $b['score'] <=> $a['score']);

        return $rankings;
    }
}
