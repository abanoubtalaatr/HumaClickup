<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\ActivityLog;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Workspace;
use App\Services\TimeTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private TimeTrackingService $timeTrackingService
    ) {}

    public function index()
    {
        $workspaceId = session('current_workspace_id');
        
        if (!$workspaceId) {
            // Redirect to workspaces if no workspace selected
            $workspace = auth()->user()->workspaces()->first();
            if ($workspace) {
                session(['current_workspace_id' => $workspace->id]);
                return redirect()->route('dashboard');
            }
            return redirect()->route('no-workspace');
        }

        $currentUser = auth()->user();
        
        // Check if admin is viewing as another user (impersonation mode)
        $impersonatingUserId = session('impersonating_user_id');
        if ($impersonatingUserId && $currentUser->isAdminInWorkspace($workspaceId)) {
            $targetUser = User::find($impersonatingUserId);
            if ($targetUser && $targetUser->belongsToWorkspace($workspaceId)) {
                $role = $targetUser->getRoleInWorkspace($workspaceId);
                
                // Get dashboard data for the impersonated user
                return match($role) {
                    'guest' => $this->guestDashboard($targetUser, $workspaceId, true),
                    'member' => $this->memberDashboard($targetUser, $workspaceId, true),
                    'admin', 'owner' => $this->adminDashboard($targetUser, $workspaceId, true),
                    default => $this->guestDashboard($targetUser, $workspaceId, true),
                };
            } else {
                // Invalid impersonation - clear it
                session()->forget('impersonating_user_id');
            }
        }

        $role = $currentUser->getRoleInWorkspace($workspaceId);
        
        // Route to role-specific dashboard
        return match($role) {
            'guest' => $this->guestDashboard($currentUser, $workspaceId),
            'member' => $this->memberDashboard($currentUser, $workspaceId),
            'admin', 'owner' => $this->adminDashboard($currentUser, $workspaceId),
            default => $this->guestDashboard($currentUser, $workspaceId),
        };
    }

    /**
     * Guest Dashboard - Personal productivity focus
     */
    private function guestDashboard(User $user, int $workspaceId, bool $isImpersonating = false)
    {
        // Time summaries for different periods
        $timeSummaries = [
            'today' => $this->getTimeSummary($user, $workspaceId, now()->startOfDay(), now()),
            'this_week' => $this->getTimeSummary($user, $workspaceId, now()->startOfWeek(), now()),
            'two_weeks' => $this->getTimeSummary($user, $workspaceId, now()->subWeeks(2)->startOfDay(), now()),
            'three_weeks' => $this->getTimeSummary($user, $workspaceId, now()->subWeeks(3)->startOfDay(), now()),
            'four_weeks' => $this->getTimeSummary($user, $workspaceId, now()->subWeeks(4)->startOfDay(), now()),
        ];

        // Get assigned tasks
        $myTasks = Task::where('workspace_id', $workspaceId)
            ->whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->with(['status', 'project', 'estimations'])
            ->orderBy('due_date')
            ->get();

        // Split tasks by status
        // Guests should see: todo, in_progress, and specific statuses like 'in review', 'retesting', 'blocked'
        $pendingTasks = $myTasks->filter(function($t) {
            if (!$t->status) return true; // Show tasks without status
            
            // Show all non-done tasks
            if ($t->status->type !== 'done') return true;
            
            // Also show specific 'done' type statuses that guests need to see
            $statusName = strtolower($t->status->name);
            $guestVisibleDoneStatuses = ['done - testing', 'ready for review', 'in review', 'retesting', 'blocked'];
            
            foreach ($guestVisibleDoneStatuses as $visibleStatus) {
                if (str_contains($statusName, strtolower($visibleStatus))) {
                    return true;
                }
            }
            
            return false;
        });
        
        // Completed tasks are only those truly done (excluding test/review statuses)
        $completedTasks = $myTasks->filter(function($t) {
            if (!$t->status || $t->status->type !== 'done') return false;
            
            $statusName = strtolower($t->status->name);
            $guestVisibleDoneStatuses = ['done - testing', 'ready for review', 'in review', 'retesting', 'blocked'];
            
            foreach ($guestVisibleDoneStatuses as $visibleStatus) {
                if (str_contains($statusName, strtolower($visibleStatus))) {
                    return false; // Don't include in completed if it's a test/review status
                }
            }
            
            return true;
        })->take(5);

        // Estimation Polling Tasks - Tasks that need estimation from this guest
        $estimationPollingTasks = $myTasks->filter(function ($task) use ($user) {
            // Task needs estimation (has guest assignees and not completed)
            if ($task->estimation_status === 'completed') {
                return false;
            }
            // Check if this task has guest assignees (needs estimation)
            return $task->getGuestAssignees()->isNotEmpty();
        })->map(function ($task) use ($user) {
            $myEstimation = $task->getEstimationByUser($user->id);
            return [
                'task' => $task,
                'has_estimated' => $myEstimation !== null,
                'my_estimation' => $myEstimation,
                'progress' => $task->getEstimationProgress(),
            ];
        });

        // Recent time entries
        $recentTimeEntries = TimeEntry::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->whereNotNull('end_time')
            ->with(['task.project'])
            ->orderBy('start_time', 'desc')
            ->limit(10)
            ->get();

        // Get assigned projects
        $assignedProjects = Project::where('workspace_id', $workspaceId)
            ->whereHas('tasks', function ($query) use ($user) {
                $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
            })
            ->withCount(['tasks' => function ($query) use ($user) {
                $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
            }])
            ->get();

        $impersonatingUser = $isImpersonating ? $user : null;
        
        return view('dashboard.guest', compact(
            'timeSummaries', 
            'myTasks', 
            'pendingTasks', 
            'completedTasks', 
            'recentTimeEntries',
            'assignedProjects',
            'estimationPollingTasks',
            'impersonatingUser'
        ));
    }

    /**
     * Member Dashboard - Team oversight and project management
     */
    private function memberDashboard(User $user, int $workspaceId, bool $isImpersonating = false)
    {
        $workspace = Workspace::find($workspaceId);

        // Personal time summary
        $myTimeSummary = $this->getTimeSummary($user, $workspaceId, now()->startOfWeek(), now());

        // Get only guests created by this member
        $myGuestIds = $workspace->users()
            ->wherePivot('role', 'guest')
            ->wherePivot('created_by_user_id', $user->id)
            ->pluck('users.id')
            ->toArray();

        // Team time tracking overview - Only for this member's guests + self
        $teamTimeTracking = $this->getTeamTimeTrackingForMember($workspaceId, $user->id, $myGuestIds);

        // Projects - Testing track members see all projects like admins
        if ($user->hasTestingTrackInWorkspace($workspaceId)) {
            $myProjects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->withCount('tasks')
                ->with(['customStatuses'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();

            $myProjectIds = Project::where('workspace_id', $workspaceId)
                ->pluck('id')
                ->toArray();

            $overdueProjects = Project::where('workspace_id', $workspaceId)
                ->overdue()
                ->get();

            $projectsDueSoon = Project::where('workspace_id', $workspaceId)
                ->dueSoon(7)
                ->get();
        } else {
            // Regular members - only their own projects
            $myProjects = Project::where('workspace_id', $workspaceId)
                ->where('created_by_user_id', $user->id)
                ->where('is_archived', false)
                ->withCount('tasks')
                ->with(['customStatuses'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();

            $myProjectIds = Project::where('workspace_id', $workspaceId)
                ->where('created_by_user_id', $user->id)
                ->pluck('id')
                ->toArray();

            $overdueProjects = Project::where('workspace_id', $workspaceId)
                ->where('created_by_user_id', $user->id)
                ->overdue()
                ->get();

            $projectsDueSoon = Project::where('workspace_id', $workspaceId)
                ->where('created_by_user_id', $user->id)
                ->dueSoon(7)
                ->get();
        }

        // Overdue tasks only from projects created by this member
        $overdueTasks = Task::where('workspace_id', $workspaceId)
            ->whereIn('project_id', $myProjectIds)
            ->overdue()
            ->with(['project', 'assignees'])
            ->limit(10)
            ->get();

        // Top time trackers leaderboard - Only this member's guests
        $topTimeTrackers = $this->getTopTimeTrackersForMember($workspaceId, $user->id, $myGuestIds, 'week');

        // Guests created by this member only
        $guests = $workspace->users()
            ->wherePivot('role', 'guest')
            ->wherePivot('created_by_user_id', $user->id)
            ->withPivot('track', 'created_by_user_id')
            ->get();

        // My assigned tasks
        $myTasks = Task::where('workspace_id', $workspaceId)
            ->whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->whereHas('status', fn($q) => $q->where('type', '!=', 'done'))
            ->with(['status', 'project'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Recent activity - Only for this member and their guests
        $relevantUserIds = array_merge([$user->id], $myGuestIds);
        $recentActivity = ActivityLog::where('workspace_id', $workspaceId)
            ->whereIn('user_id', $relevantUserIds)
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Estimation Polling Overview - Tasks in polling state
        $estimationPollingTasks = Task::where('workspace_id', $workspaceId)
            ->where(function ($q) {
                $q->where('estimation_status', 'polling')
                    ->orWhereHas('estimations');
            })
            ->with(['project', 'status', 'assignees', 'estimations.user'])
            ->orderByRaw("CASE 
                WHEN estimation_status = 'polling' THEN 1 
                WHEN estimation_status = 'completed' THEN 2 
                ELSE 3 
            END")
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'task' => $task,
                    'progress' => $task->getEstimationProgress(),
                    'estimations' => $task->estimations->map(fn($e) => [
                        'user_name' => $e->user->name,
                        'minutes' => $e->estimated_minutes,
                        'formatted' => $e->getFormattedEstimation(),
                    ]),
                ];
            });

        $impersonatingUser = $isImpersonating ? $user : null;
        
        return view('dashboard.member', compact(
            'myTimeSummary',
            'teamTimeTracking',
            'myProjects',
            'overdueProjects',
            'projectsDueSoon',
            'overdueTasks',
            'topTimeTrackers',
            'guests',
            'myTasks',
            'recentActivity',
            'estimationPollingTasks',
            'impersonatingUser'
        ));
    }

    /**
     * Admin Dashboard - Complete workspace visibility
     */
    private function adminDashboard(User $user, int $workspaceId, bool $isImpersonating = false)
    {
        $workspace = Workspace::find($workspaceId);

        // Overall stats
        $stats = [
            'total_projects' => Project::where('workspace_id', $workspaceId)->count(),
            'active_projects' => Project::where('workspace_id', $workspaceId)->where('is_archived', false)->count(),
            'total_tasks' => Task::where('workspace_id', $workspaceId)->count(),
            'completed_tasks' => Task::where('workspace_id', $workspaceId)
                ->whereHas('status', fn($q) => $q->where('type', 'done'))
                ->count(),
            'total_members' => $workspace->users()->count(),
            'total_time_week' => $this->formatDuration(
                TimeEntry::where('workspace_id', $workspaceId)
                    ->whereBetween('start_time', [now()->startOfWeek(), now()])
                    ->sum('duration')
            ),
        ];

        // All projects with progress
        $allProjects = Project::where('workspace_id', $workspaceId)
            ->where('is_archived', false)
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn($q) => 
                $q->whereHas('status', fn($sq) => $sq->where('type', 'done'))
            ])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Due date alerts
        $overdueProjects = Project::where('workspace_id', $workspaceId)
            ->overdue()
            ->get();

        $projectsDueSoon = Project::where('workspace_id', $workspaceId)
            ->dueSoon(7)
            ->get();

        $overdueTasks = Task::where('workspace_id', $workspaceId)
            ->overdue()
            ->with(['project', 'assignees'])
            ->limit(15)
            ->get();

        // Team time tracking by user
        $teamTimeTracking = $this->getTeamTimeTracking($workspaceId);

        // Top time trackers leaderboard (desc)
        $topTimeTrackers = $this->getTopTimeTrackers($workspaceId, 'week');

        // All workspace members with roles and tracks
        $allMembers = $workspace->users()
            ->withPivot(['role', 'track'])
            ->get()
            ->sortBy(function ($user) {
                return ['owner' => 0, 'admin' => 1, 'member' => 2, 'guest' => 3][$user->pivot->role] ?? 4;
            });

        // Inactive users (no time tracked in last 7 days)
        $activeUserIds = TimeEntry::where('workspace_id', $workspaceId)
            ->where('start_time', '>=', now()->subDays(7))
            ->pluck('user_id')
            ->unique();
        
        $inactiveUsers = $workspace->users()
            ->whereNotIn('users.id', $activeUserIds)
            ->withPivot(['role', 'track'])
            ->get();

        // Tasks without assignees
        $unassignedTasks = Task::where('workspace_id', $workspaceId)
            ->whereHas('status', fn($q) => $q->where('type', '!=', 'done'))
            ->whereDoesntHave('assignees')
            ->with('project')
            ->limit(10)
            ->get();

        // Recent activity
        $recentActivity = ActivityLog::where('workspace_id', $workspaceId)
            ->with('user')
            ->latest()
            ->limit(15)
            ->get();

        // Estimation Polling Overview - All tasks with estimation data
        $estimationPollingTasks = Task::where('workspace_id', $workspaceId)
            ->where(function ($q) {
                $q->where('estimation_status', 'polling')
                    ->orWhereHas('estimations');
            })
            ->with(['project', 'status', 'assignees', 'estimations.user'])
            ->orderByRaw("CASE 
                WHEN estimation_status = 'polling' THEN 1 
                WHEN estimation_status = 'completed' THEN 2 
                ELSE 3 
            END")
            ->orderBy('updated_at', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($task) {
                return [
                    'task' => $task,
                    'progress' => $task->getEstimationProgress(),
                    'estimations' => $task->estimations->map(fn($e) => [
                        'user_name' => $e->user->name,
                        'minutes' => $e->estimated_minutes,
                        'formatted' => $e->getFormattedEstimation(),
                    ]),
                    'guest_assignees' => $task->getGuestAssignees()->map(fn($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'has_estimated' => $task->hasEstimationFromUser($u->id),
                    ]),
                ];
            });

        // Estimation Summary
        $estimationSummary = [
            'polling' => Task::where('workspace_id', $workspaceId)->where('estimation_status', 'polling')->count(),
            'completed' => Task::where('workspace_id', $workspaceId)->where('estimation_status', 'completed')->count(),
            'pending' => Task::where('workspace_id', $workspaceId)
                ->where('estimation_status', 'pending')
                ->whereHas('assignees', function ($q) use ($workspace) {
                    $q->whereIn('users.id', $workspace->users()
                        ->wherePivot('role', 'guest')
                        ->pluck('users.id'));
                })
                ->count(),
        ];

        $impersonatingUser = $isImpersonating ? $user : null;
        
        return view('dashboard.admin', compact(
            'stats',
            'allProjects',
            'overdueProjects',
            'projectsDueSoon',
            'overdueTasks',
            'teamTimeTracking',
            'topTimeTrackers',
            'allMembers',
            'inactiveUsers',
            'unassignedTasks',
            'recentActivity',
            'estimationPollingTasks',
            'estimationSummary',
            'impersonatingUser'
        ));
    }

    /**
     * Get time summary for a user in a workspace
     */
    private function getTimeSummary(User $user, int $workspaceId, Carbon $start, Carbon $end): array
    {
        $totalSeconds = TimeEntry::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->whereBetween('start_time', [$start, $end])
            ->sum('duration');

        return [
            'total_seconds' => $totalSeconds,
            'total_formatted' => $this->formatDuration($totalSeconds),
            'hours' => floor($totalSeconds / 3600),
            'minutes' => floor(($totalSeconds % 3600) / 60),
        ];
    }

    /**
     * Get team time tracking overview
     */
    private function getTeamTimeTracking(int $workspaceId): array
    {
        $workspace = Workspace::find($workspaceId);
        $users = $workspace->users()->withPivot(['role', 'track'])->get();

        $tracking = [];
        foreach ($users as $user) {
            $weeklyTime = TimeEntry::where('workspace_id', $workspaceId)
                ->where('user_id', $user->id)
                ->whereBetween('start_time', [now()->startOfWeek(), now()])
                ->sum('duration');

            $tracking[] = [
                'user' => $user,
                'role' => $user->pivot->role,
                'track' => $user->pivot->track,
                'weekly_seconds' => $weeklyTime,
                'weekly_formatted' => $this->formatDuration($weeklyTime),
            ];
        }

        return $tracking;
    }

    /**
     * Get top time trackers (leaderboard)
     */
    private function getTopTimeTrackers(int $workspaceId, string $period = 'week'): array
    {
        $startDate = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };

        $workspace = Workspace::find($workspaceId);
        
        $results = TimeEntry::where('workspace_id', $workspaceId)
            ->where('start_time', '>=', $startDate)
            ->selectRaw('user_id, SUM(duration) as total_duration')
            ->groupBy('user_id')
            ->orderByDesc('total_duration')
            ->limit(10)
            ->get();

        $trackers = [];
        foreach ($results as $result) {
            $user = User::find($result->user_id);
            if ($user) {
                $pivot = $workspace->users()->where('user_id', $user->id)->first()?->pivot;
                $trackers[] = [
                    'user' => $user,
                    'role' => $pivot?->role ?? 'unknown',
                    'track' => $pivot?->track ?? null,
                    'total_seconds' => $result->total_duration,
                    'total_formatted' => $this->formatDuration($result->total_duration),
                ];
            }
        }

        return $trackers;
    }

    /**
     * Get team time tracking for a specific member (member + their guests only)
     */
    private function getTeamTimeTrackingForMember(int $workspaceId, int $memberId, array $guestIds): array
    {
        $workspace = Workspace::find($workspaceId);
        $userIds = array_merge([$memberId], $guestIds);
        
        $users = $workspace->users()
            ->whereIn('users.id', $userIds)
            ->withPivot(['role', 'track'])
            ->get();

        $tracking = [];
        foreach ($users as $user) {
            $weeklyTime = TimeEntry::where('workspace_id', $workspaceId)
                ->where('user_id', $user->id)
                ->whereBetween('start_time', [now()->startOfWeek(), now()])
                ->sum('duration');

            $tracking[] = [
                'user' => $user,
                'role' => $user->pivot->role,
                'track' => $user->pivot->track,
                'weekly_seconds' => $weeklyTime,
                'weekly_formatted' => $this->formatDuration($weeklyTime),
            ];
        }

        // Sort by weekly time descending
        usort($tracking, fn($a, $b) => $b['weekly_seconds'] <=> $a['weekly_seconds']);

        return $tracking;
    }

    /**
     * Get top time trackers for a specific member's team (only their guests)
     */
    private function getTopTimeTrackersForMember(int $workspaceId, int $memberId, array $guestIds, string $period = 'week'): array
    {
        $startDate = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };

        $workspace = Workspace::find($workspaceId);
        
        // Only include the member's guests (not the member themselves, not admins)
        if (empty($guestIds)) {
            return [];
        }
        
        $results = TimeEntry::where('workspace_id', $workspaceId)
            ->whereIn('user_id', $guestIds)
            ->where('start_time', '>=', $startDate)
            ->selectRaw('user_id, SUM(duration) as total_duration')
            ->groupBy('user_id')
            ->orderByDesc('total_duration')
            ->limit(10)
            ->get();

        $trackers = [];
        foreach ($results as $result) {
            $user = User::find($result->user_id);
            if ($user) {
                $pivot = $workspace->users()->where('user_id', $user->id)->first()?->pivot;
                $trackers[] = [
                    'user' => $user,
                    'role' => $pivot?->role ?? 'unknown',
                    'track' => $pivot?->track ?? null,
                    'total_seconds' => $result->total_duration,
                    'total_formatted' => $this->formatDuration($result->total_duration),
                ];
            }
        }

        return $trackers;
    }

    /**
     * Format duration in seconds to human readable string
     */
    private function formatDuration(?int $seconds): string
    {
        if (!$seconds) return '0h 0m';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return "{$hours}h {$minutes}m";
    }

    /**
     * Start viewing as another user (Admin only) - Impersonation mode
     */
    public function viewAsUser(User $targetUser)
    {
        $workspaceId = session('current_workspace_id');
        $currentUser = auth()->user();

        // Only admins can view other users' dashboards
        if (!$currentUser->isAdminInWorkspace($workspaceId)) {
            abort(403, 'Only admins can view other user dashboards.');
        }

        // Verify target user belongs to this workspace
        if (!$targetUser->belongsToWorkspace($workspaceId)) {
            abort(404, 'User not found in this workspace.');
        }

        // Can't impersonate yourself
        if ($targetUser->id === $currentUser->id) {
            return redirect()->route('dashboard')->with('info', 'You are already viewing your own dashboard.');
        }

        // Set impersonation session
        session(['impersonating_user_id' => $targetUser->id]);

        return redirect()->route('dashboard')->with('success', 'Now viewing dashboard as ' . $targetUser->name);
    }

    /**
     * Stop viewing as another user - Return to admin view
     */
    public function stopImpersonating()
    {
        session()->forget('impersonating_user_id');
        
        return redirect()->route('dashboard')->with('success', 'Returned to your admin dashboard.');
    }

    /**
     * Get guest dashboard data (for view-as-user)
     */
    private function getGuestDashboardData(User $user, int $workspaceId): array
    {
        $timeSummaries = [
            'today' => $this->getTimeSummary($user, $workspaceId, now()->startOfDay(), now()),
            'this_week' => $this->getTimeSummary($user, $workspaceId, now()->startOfWeek(), now()),
            'two_weeks' => $this->getTimeSummary($user, $workspaceId, now()->subWeeks(2)->startOfDay(), now()),
            'three_weeks' => $this->getTimeSummary($user, $workspaceId, now()->subWeeks(3)->startOfDay(), now()),
            'four_weeks' => $this->getTimeSummary($user, $workspaceId, now()->subWeeks(4)->startOfDay(), now()),
        ];

        $myTasks = Task::where('workspace_id', $workspaceId)
            ->whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->with(['status', 'project', 'estimations'])
            ->orderBy('due_date')
            ->get();

        $pendingTasks = $myTasks->filter(fn($t) => $t->status?->type !== 'done');
        $completedTasks = $myTasks->filter(fn($t) => $t->status?->type === 'done')->take(5);

        $estimationPollingTasks = $myTasks->filter(function ($task) use ($user) {
            if ($task->estimation_status === 'completed') {
                return false;
            }
            return $task->getGuestAssignees()->isNotEmpty();
        })->map(function ($task) use ($user) {
            $myEstimation = $task->getEstimationByUser($user->id);
            return [
                'task' => $task,
                'has_estimated' => $myEstimation !== null,
                'my_estimation' => $myEstimation,
                'progress' => $task->getEstimationProgress(),
            ];
        });

        $recentTimeEntries = TimeEntry::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->whereNotNull('end_time')
            ->with(['task.project'])
            ->orderBy('start_time', 'desc')
            ->limit(10)
            ->get();

        $assignedProjects = Project::where('workspace_id', $workspaceId)
            ->whereHas('tasks', function ($query) use ($user) {
                $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
            })
            ->withCount(['tasks' => function ($query) use ($user) {
                $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
            }])
            ->get();

        return compact(
            'timeSummaries', 
            'myTasks', 
            'pendingTasks', 
            'completedTasks', 
            'recentTimeEntries',
            'assignedProjects',
            'estimationPollingTasks'
        );
    }

    /**
     * Get member dashboard data (for view-as-user)
     */
    private function getMemberDashboardData(User $user, int $workspaceId): array
    {
        $workspace = Workspace::find($workspaceId);

        // Get only guests created by this member
        $myGuestIds = $workspace->users()
            ->wherePivot('role', 'guest')
            ->wherePivot('created_by_user_id', $user->id)
            ->pluck('users.id')
            ->toArray();

        $myTimeSummary = $this->getTimeSummary($user, $workspaceId, now()->startOfWeek(), now());
        $teamTimeTracking = $this->getTeamTimeTrackingForMember($workspaceId, $user->id, $myGuestIds);

        // Projects created by this member only
        $myProjects = Project::where('workspace_id', $workspaceId)
            ->where('created_by_user_id', $user->id)
            ->where('is_archived', false)
            ->withCount('tasks')
            ->with(['customStatuses'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $myTasks = Task::where('workspace_id', $workspaceId)
            ->whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->whereHas('status', fn($q) => $q->where('type', '!=', 'done'))
            ->with(['status', 'project'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Only guests created by this member
        $guests = $workspace->users()
            ->wherePivot('role', 'guest')
            ->wherePivot('created_by_user_id', $user->id)
            ->withPivot('track', 'created_by_user_id')
            ->get();

        $estimationPollingTasks = Task::where('workspace_id', $workspaceId)
            ->where(function ($q) {
                $q->where('estimation_status', 'polling')
                    ->orWhereHas('estimations');
            })
            ->with(['project', 'status', 'assignees', 'estimations.user'])
            ->orderByRaw("CASE 
                WHEN estimation_status = 'polling' THEN 1 
                WHEN estimation_status = 'completed' THEN 2 
                ELSE 3 
            END")
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'task' => $task,
                    'progress' => $task->getEstimationProgress(),
                    'estimations' => $task->estimations->map(fn($e) => [
                        'user_name' => $e->user->name,
                        'minutes' => $e->estimated_minutes,
                        'formatted' => $e->getFormattedEstimation(),
                    ]),
                ];
            });

        return compact(
            'myTimeSummary',
            'teamTimeTracking',
            'myProjects',
            'myTasks',
            'guests',
            'estimationPollingTasks'
        );
    }

    /**
     * Get admin dashboard data (for view-as-user)
     */
    private function getAdminDashboardData(User $user, int $workspaceId): array
    {
        $workspace = Workspace::find($workspaceId);

        $stats = [
            'total_projects' => Project::where('workspace_id', $workspaceId)->count(),
            'active_projects' => Project::where('workspace_id', $workspaceId)->where('is_archived', false)->count(),
            'total_tasks' => Task::where('workspace_id', $workspaceId)->count(),
            'completed_tasks' => Task::where('workspace_id', $workspaceId)
                ->whereHas('status', fn($q) => $q->where('type', 'done'))
                ->count(),
            'total_members' => $workspace->users()->count(),
            'total_time_week' => $this->formatDuration(
                TimeEntry::where('workspace_id', $workspaceId)
                    ->whereBetween('start_time', [now()->startOfWeek(), now()])
                    ->sum('duration')
            ),
        ];

        $allProjects = Project::where('workspace_id', $workspaceId)
            ->where('is_archived', false)
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn($q) => 
                $q->whereHas('status', fn($sq) => $sq->where('type', 'done'))
            ])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $allMembers = $workspace->users()
            ->withPivot(['role', 'track'])
            ->get()
            ->sortBy(function ($user) {
                return ['owner' => 0, 'admin' => 1, 'member' => 2, 'guest' => 3][$user->pivot->role] ?? 4;
            });

        return compact('stats', 'allProjects', 'allMembers');
    }
}
