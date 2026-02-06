<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\ProgressTrackingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProgressDashboardController extends Controller
{
    protected ProgressTrackingService $progressService;

    public function __construct(ProgressTrackingService $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * Owner/Admin overview dashboard.
     */
    public function ownerOverview(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        // Check if user is owner or admin
        if (!$user->isOwnerInWorkspace($workspaceId) && !$user->isAdminInWorkspace($workspaceId)) {
            abort(403, 'Only owners and admins can access this dashboard.');
        }
        
        $workspace = \App\Models\Workspace::findOrFail($workspaceId);
        $projects = $workspace->projects()
            ->with('group.guests')
            ->where('is_archived', false)
            ->get();
        
        // Collect stats
        $stats = [];
        foreach ($projects as $project) {
            $membersWithoutTasks = $project->getMembersWithoutTasks();
            $membersWithOverdueTasks = $project->getMembersWithOverdueTasks();
            $membersNotMeetingTarget = $project->getMembersNotMeetingWeeklyTarget();
            
            $stats[] = [
                'project' => $project,
                'members_without_tasks' => $membersWithoutTasks,
                'members_with_overdue_tasks' => $membersWithOverdueTasks,
                'members_not_meeting_target' => $membersNotMeetingTarget,
                'completion' => $this->progressService->calculateProjectCompletion($project),
            ];
        }
        
        return view('dashboards.owner-overview', compact('workspace', 'stats'));
    }

    /**
     * Mentor dashboard for attendance and progress checks.
     */
    public function mentorDashboard(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        // Check if user is member (mentor)
        if (!$user->isMemberInWorkspace($workspaceId)) {
            abort(403, 'Only members can access mentor dashboard.');
        }
        
        // Get guests created by this mentor
        $guests = $user->getCreatedGuestsInWorkspace($workspaceId);
        
        // Get pending attendance checks for mentor's guests
        $pendingAttendances = \App\Models\Attendance::whereIn('guest_id', $guests->pluck('id'))
            ->where('checked_by_mentor', false)
            ->with(['guest', 'project'])
            ->latest('date')
            ->take(50)
            ->get();
        
        // Get today's progress for each guest
        $guestProgress = [];
        foreach ($guests as $guest) {
            $projects = \App\Models\Project::whereHas('group.guests', function ($q) use ($guest) {
                $q->where('users.id', $guest->id);
            })->get();
            
            foreach ($projects as $project) {
                $todayProgress = $this->progressService->getDailyProgress($guest, $project, today());
                
                $guestProgress[] = [
                    'guest' => $guest,
                    'project' => $project,
                    'progress' => $todayProgress,
                    'weekly_progress' => $guest->getWeeklyProgress(),
                ];
            }
        }
        
        return view('dashboards.mentor-dashboard', compact('pendingAttendances', 'guestProgress'));
    }

    /**
     * Project progress dashboard.
     */
    public function projectProgress(Project $project, Request $request)
    {
        $this->authorize('view', $project);
        
        $date = $request->date('date', today());
        
        $overview = $this->progressService->getProjectProgressOverview($project, $date);
        $completion = $this->progressService->calculateProjectCompletion($project);
        $ranking = $this->progressService->getTeamPerformanceRanking($project);
        
        return view('dashboards.project-progress', compact('project', 'overview', 'completion', 'ranking', 'date'));
    }

    /**
     * User progress dashboard.
     */
    public function userProgress(Project $project, User $user, Request $request)
    {
        $this->authorize('view', $project);
        
        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);
        
        $weekStart = $request->date('week_start', now()->startOfWeek());
        
        $weeklyProgress = $this->progressService->getWeeklyProgressSummary($user, $project, $weekStart);
        $monthlyProgress = $this->progressService->getMonthlyProgressReport($user, $project, $month, $year);
        
        return view('dashboards.user-progress', compact('project', 'user', 'weeklyProgress', 'monthlyProgress'));
    }

    /**
     * Team performance ranking.
     */
    public function teamRanking(Project $project, Request $request)
    {
        $this->authorize('view', $project);
        
        $startDate = $request->date('start_date', now()->startOfMonth());
        $endDate = $request->date('end_date', now()->endOfMonth());
        
        $ranking = $this->progressService->getTeamPerformanceRanking($project, $startDate, $endDate);
        
        return view('dashboards.team-ranking', compact('project', 'ranking', 'startDate', 'endDate'));
    }
}
