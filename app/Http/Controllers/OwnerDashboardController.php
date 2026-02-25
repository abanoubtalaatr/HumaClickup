<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Workspace;
use App\Services\DailyProgressService;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OwnerDashboardController extends Controller
{
    protected DailyProgressService $progressService;
    protected AttendanceService $attendanceService;

    public function __construct(
        DailyProgressService $progressService,
        AttendanceService $attendanceService
    ) {
        $this->progressService = $progressService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Show owner overview dashboard.
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Only owners/admins can access
        if (!$user->isOwnerInWorkspace($workspaceId) && !$user->isAdminInWorkspace($workspaceId)) {
            abort(403, 'Only owners and admins can access this dashboard.');
        }

        $workspace = Workspace::findOrFail($workspaceId);
        $date = $request->date('date', today());

        // Get all active projects
        $projects = $workspace->projects()
            ->where('is_archived', false)
            ->withCount('projectMembers')
            ->get();

        // Collect stats per project
        $projectStats = [];

        foreach ($projects as $project) {
            $guests = $project->getGuestMembers();
            
            // Guests without tasks today
            $guestsWithoutTasks = $this->progressService->getGuestsWithoutMainTask($project, $date);
            
            // Guests with incomplete progress
            $guestsWithIncomplete = $this->progressService->getGuestsWithIncompleteProgress($project, $date);
            
            // Get today's progress for all guests
            $todayProgress = \App\Models\DailyProgress::where('project_id', $project->id)
                ->where('date', $date)
                ->get();

            $avgProgress = $todayProgress->avg('progress_percentage') ?? 0;
            $totalHours = $todayProgress->sum('completed_hours');

            // Get attendance stats
            $todayAttendance = \App\Models\Attendance::where('project_id', $project->id)
                ->where('date', $date)
                ->get();

            $presentCount = $todayAttendance->where('status', 'present')->count();
            $absentCount = $todayAttendance->where('status', 'absent')->count();

            $projectStats[] = [
                'project' => $project,
                'guests_count' => $guests->count(),
                'guests_without_tasks' => $guestsWithoutTasks,
                'guests_with_incomplete' => $guestsWithIncomplete,
                'average_progress' => round($avgProgress, 1),
                'total_hours_today' => round($totalHours, 1),
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
            ];
        }

        // Global workspace stats
        $globalStats = [
            'total_projects' => $projects->count(),
            'total_guests' => $projects->sum('project_members_count'),
            'total_guests_without_tasks' => collect($projectStats)->sum(fn($s) => $s['guests_without_tasks']->count()),
            'total_pending_approvals' => \App\Models\DailyProgress::whereIn('project_id', $projects->pluck('id'))
                ->where('date', $date)
                ->where('approved', false)
                ->count(),
        ];

        return view('owner.overview', compact('workspace', 'projectStats', 'globalStats', 'date'));
    }

    /**
     * Show project details for owner.
     */
    public function showProject(Project $project, Request $request)
    {
        $user = auth()->user();

        if (!$user->isOwnerInWorkspace($project->workspace_id) && 
            !$user->isAdminInWorkspace($project->workspace_id)) {
            abort(403);
        }

        $startDate = $request->date('start_date', now()->startOfMonth());
        $endDate = $request->date('end_date', now()->endOfMonth());

        $guests = $project->getGuestMembers();

        // Get progress for each guest
        $guestStats = [];

        foreach ($guests as $guest) {
            $weekStart = now()->startOfWeek();
            $weeklyProgress = $this->progressService->calculateWeeklyProgress($guest, $project, $weekStart);
            $attendanceSummary = $this->attendanceService->getAttendanceSummary($guest, $project, $startDate, $endDate);

            $guestStats[] = [
                'guest' => $guest,
                'weekly_progress' => $weeklyProgress,
                'attendance_summary' => $attendanceSummary,
            ];
        }

        return view('owner.project-details', compact('project', 'guestStats', 'startDate', 'endDate'));
    }

    /**
     * Show guests without tasks across all projects.
     */
    public function guestsWithoutTasks(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if (!$user->isOwnerInWorkspace($workspaceId) && !$user->isAdminInWorkspace($workspaceId)) {
            abort(403);
        }

        $workspace = Workspace::findOrFail($workspaceId);
        $date = $request->date('date', today());

        $projects = $workspace->projects()->where('is_archived', false)->get();

        $result = [];

        foreach ($projects as $project) {
            $guestsWithoutTasks = $this->progressService->getGuestsWithoutMainTask($project, $date);
            
            foreach ($guestsWithoutTasks as $guest) {
                $result[] = [
                    'guest' => $guest,
                    'project' => $project,
                    'date' => $date,
                ];
            }
        }

        return view('owner.guests-without-tasks', compact('result', 'date'));
    }
}
