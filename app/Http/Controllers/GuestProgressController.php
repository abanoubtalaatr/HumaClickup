<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\DailyProgressService;
use App\Services\AttendanceService;
use App\Services\AbsenceTrackingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GuestProgressController extends Controller
{
    protected DailyProgressService $progressService;
    protected AttendanceService $attendanceService;
    protected AbsenceTrackingService $absenceTrackingService;

    public function __construct(
        DailyProgressService $progressService,
        AttendanceService $attendanceService,
        AbsenceTrackingService $absenceTrackingService
    ) {
        $this->progressService = $progressService;
        $this->attendanceService = $attendanceService;
        $this->absenceTrackingService = $absenceTrackingService;
    }

    /**
     * Show guest's own progress dashboard.
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Get guest's projects
        $projects = Project::where('workspace_id', $workspaceId)
            ->whereHas('projectMembers', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('role', 'guest');
            })
            ->get();

        $date = $request->date('date') ?? today();
        
        // Ensure date is a Carbon instance
        if (!$date instanceof \Carbon\Carbon) {
            $date = \Carbon\Carbon::parse($date);
        }

        // Get today's progress for each project
        $projectProgress = [];

        foreach ($projects as $project) {
            $dailyProgress = \App\Models\DailyProgress::where('user_id', $user->id)
                ->where('project_id', $project->id)
                ->where('date', $date)
                ->first();

            // Calculate if not exists
            if (!$dailyProgress) {
                $dailyProgress = $this->progressService->calculateDailyProgress($user, $project, $date);
            }

            $projectProgress[] = [
                'project' => $project,
                'progress' => $dailyProgress,
                'attendance' => $this->attendanceService->getAttendance($user, $project, $date),
            ];
        }

        // Weekly summary: from main tasks completed this week only (count × 6h), target 30h
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $projectIds = $projects->pluck('id');
        $weeklyCompletedMainTasks = \App\Models\Task::whereIn('project_id', $projectIds)
            ->where('is_main_task', 'yes')
            ->whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->whereHas('status', fn($q) => $q->where('type', 'done'))
            ->whereNotNull('completion_date')
            ->whereBetween('completion_date', [$weekStart, $weekEnd])
            ->count();
        $hoursPerTask = 6;
        $weeklyTargetHours = 30;
        $weeklyHours = $weeklyCompletedMainTasks * $hoursPerTask;
        $weeklyProgressPct = $weeklyTargetHours > 0 ? min(($weeklyHours / $weeklyTargetHours) * 100, 100) : 0;
        $meetsWeeklyTarget = $weeklyHours >= $weeklyTargetHours;

        $weeklySummary = [
            'total_hours' => (int) $weeklyHours,
            'average_progress' => round($weeklyProgressPct, 1),
            'meets_target' => $meetsWeeklyTarget,
            'target_hours' => $weeklyTargetHours,
        ];

        $totalAbsenceDays = $this->absenceTrackingService->getTotalAbsenceDaysForGuest($workspaceId, $user->id);

        return view('guests.progress', compact(
            'projectProgress',
            'weeklySummary',
            'date',
            'totalAbsenceDays'
        ));
    }

    /**
     * Show progress for a specific project.
     */
    public function show(Project $project, Request $request)
    {
        $user = auth()->user();

        // Check if user is a guest member of this project
        if (!$project->hasGuestMember($user)) {
            abort(403, 'You are not a member of this project.');
        }

        $date = $request->date('date', today());

        // Calculate daily progress
        $dailyProgress = $this->progressService->calculateDailyProgress($user, $project, $date);

        // Get weekly progress
        $weekStart = $date->copy()->startOfWeek();
        $weeklyProgress = $this->progressService->calculateWeeklyProgress($user, $project, $weekStart);

        // Get attendance
        $attendance = $this->attendanceService->getAttendance($user, $project, $date);

        // Get main task for today
        $mainTask = $this->progressService->findMainTaskForDay($user, $project, $date);

        return view('guests.project-progress', compact(
            'project',
            'date',
            'dailyProgress',
            'weeklyProgress',
            'attendance',
            'mainTask'
        ));
    }

    /**
     * Show guest's calendar view.
     */
    public function calendar(Project $project, Request $request)
    {
        $user = auth()->user();

        if (!$project->hasGuestMember($user)) {
            abort(403);
        }

        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all progress for the month
        $monthlyProgress = $this->progressService->getDailyProgressRange($user, $project, $startDate, $endDate);

        // Get all attendance for the month
        $monthlyAttendance = \App\Models\Attendance::where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($a) => $a->date->format('Y-m-d'));

        return view('guests.calendar', compact(
            'project',
            'month',
            'year',
            'startDate',
            'endDate',
            'monthlyProgress',
            'monthlyAttendance'
        ));
    }
}
