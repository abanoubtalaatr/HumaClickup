<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TimeTrackingService;
use Illuminate\Http\Request;

class TimeTrackingController extends Controller
{
    public function __construct(
        private TimeTrackingService $timeTrackingService
    ) {}

    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        $isAdmin = $user->isAdminInWorkspace($workspaceId);
        $isMember = $user->isMemberOnlyInWorkspace($workspaceId);

        // If admin, show admin view with all guests
        if ($isAdmin && $request->get('view') !== 'personal') {
            return $this->adminTimeTrackingView($request, $workspaceId, $user);
        }

        // If member (not guest), show member view with their guests
        if ($isMember && $request->get('view') !== 'personal') {
            return $this->memberTimeTrackingView($request, $workspaceId, $user);
        }

        // Regular user/guest personal view
        $activeTimer = $user->getActiveTimer();
        if ($activeTimer) {
            $activeTimer->load('task.project');
        }

        $timeEntries = \App\Models\TimeEntry::where('user_id', $user->id)
            ->where('workspace_id', $workspaceId)
            ->whereNotNull('end_time')
            ->with(['task.project'])
            ->latest()
            ->limit(50)
            ->get();

        $summary = $this->timeTrackingService->getUserTimeSummary(
            $user,
            $workspaceId,
            now()->startOfWeek(),
            now()->endOfWeek()
        );

        // Get tasks for the start timer modal based on user role
        $tasks = $this->getTasksForTimeTracking($workspaceId, $user);

        return view('time-tracking.index', compact('activeTimer', 'timeEntries', 'summary', 'tasks'));
    }

    /**
     * Member view showing their guests' time tracking
     */
    private function memberTimeTrackingView(Request $request, int $workspaceId, $member)
    {
        $workspace = \App\Models\Workspace::find($workspaceId);
        
        // Get filter period (default: week)
        $period = $request->get('period', 'week');
        $startDate = $this->getStartDateForPeriod($period);
        $endDate = now();

        // Get only guests created by this member
        $guests = $workspace->users()
            ->wherePivot('role', 'guest')
            ->wherePivot('created_by_user_id', $member->id)
            ->get();

        // Get time tracking data for each guest
        $guestTimeData = $guests->map(function ($guest) use ($workspaceId, $startDate, $endDate) {
            $timeEntries = \App\Models\TimeEntry::where('workspace_id', $workspaceId)
                ->where('user_id', $guest->id)
                ->whereNotNull('end_time')
                ->whereBetween('start_time', [$startDate, $endDate])
                ->with(['task.project'])
                ->get();

            $totalDuration = $timeEntries->sum('duration');

            return [
                'user' => $guest,
                'total_duration' => $totalDuration,
                'total_minutes' => round($totalDuration / 60),
                'total_formatted' => $this->formatDuration($totalDuration),
                'entries_count' => $timeEntries->count(),
                'entries' => $timeEntries,
                'track' => $guest->getTrackInWorkspace($workspaceId),
            ];
        });

        // Get aggregated statistics
        $totalDuration = $guestTimeData->sum('total_duration');
        $totalEntries = $guestTimeData->sum('entries_count');
        
        $totalFormatted = $this->formatDuration($totalDuration);

        return view('time-tracking.member', compact(
            'guestTimeData',
            'period',
            'totalDuration',
            'totalEntries',
            'totalFormatted',
            'startDate',
            'endDate',
            'member'
        ));
    }

    /**
     * Admin view showing all guests' time tracking
     */
    private function adminTimeTrackingView(Request $request, int $workspaceId, $admin)
    {
        $workspace = \App\Models\Workspace::find($workspaceId);
        
        // Get filter period (default: week)
        $period = $request->get('period', 'week');
        $startDate = $this->getStartDateForPeriod($period);
        $endDate = now();

        // Get all guests in the workspace
        $guests = $workspace->users()
            ->wherePivot('role', 'guest')
            ->get();

        // Get time tracking data for each guest
        $guestTimeData = $guests->map(function ($guest) use ($workspaceId, $startDate, $endDate) {
            $timeEntries = \App\Models\TimeEntry::where('workspace_id', $workspaceId)
                ->where('user_id', $guest->id)
                ->whereNotNull('end_time')
                ->whereBetween('start_time', [$startDate, $endDate])
                ->with(['task.project'])
                ->get();

            $totalDuration = $timeEntries->sum('duration');

            return [
                'user' => $guest,
                'total_duration' => $totalDuration,
                'total_minutes' => round($totalDuration / 60),
                'total_formatted' => $this->formatDuration($totalDuration),
                'entries_count' => $timeEntries->count(),
                'entries' => $timeEntries,
                'track' => $guest->getTrackInWorkspace($workspaceId),
            ];
        });

        // Get aggregated statistics
        $totalDuration = $guestTimeData->sum('total_duration');
        $totalEntries = $guestTimeData->sum('entries_count');
        
        $totalFormatted = $this->formatDuration($totalDuration);

        return view('time-tracking.admin', compact(
            'guestTimeData',
            'period',
            'startDate',
            'endDate',
            'totalFormatted',
            'totalEntries'
        ));
    }

    /**
     * Format duration in seconds to hours and minutes
     */
    private function formatDuration(int $seconds): string
    {
        $minutes = (int)($seconds / 60);
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}m";
        }
    }

    /**
     * Get start date based on selected period
     */
    private function getStartDateForPeriod(string $period)
    {
        return match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            '2weeks' => now()->subWeeks(2)->startOfDay(),
            '3weeks' => now()->subWeeks(3)->startOfDay(),
            '4weeks' => now()->subWeeks(4)->startOfDay(),
            'month' => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };
    }

    /**
     * Get tasks available for time tracking based on user role
     */
    private function getTasksForTimeTracking(int $workspaceId, $user)
    {
        $query = Task::where('workspace_id', $workspaceId)
            ->with('project')
            ->whereHas('status', function ($query) {
                $query->where('type', '!=', 'done');
            });
        
        // Guests can only track time on tasks assigned to them
        if ($user->isGuestInWorkspace($workspaceId)) {
            $query->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        return $query->orderBy('updated_at', 'desc')
            ->limit(100)
            ->get();
    }

    /**
     * API endpoint to get tasks for time tracking
     */
    public function getTasksForTracking(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        $tasks = $this->getTasksForTimeTracking($workspaceId, $user);
        
        return response()->json($tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'project_name' => $task->project?->name ?? 'No Project',
                'project_id' => $task->project_id,
            ];
        }));
    }

    public function start(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'description' => 'nullable|string',
        ]);

        $task = Task::findOrFail($validated['task_id']);
        
        // Validate task is in current workspace
        if ($task->workspace_id !== $workspaceId) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found in current workspace.',
            ], 404);
        }
        
        // Guests can only track time on tasks assigned to them
        if ($user->isGuestInWorkspace($workspaceId)) {
            if (!$task->assignees->contains($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only track time on tasks assigned to you.',
                ], 403);
            }
        }
        
        $this->authorize('trackTime', $task);

        $timeEntry = $this->timeTrackingService->startTimer(
            $task,
            auth()->user(),
            $validated['description'] ?? null
        );

        return response()->json([
            'success' => true,
            'time_entry' => $timeEntry,
        ]);
    }

    public function stop(Request $request)
    {
        $user = auth()->user();
        $activeTimer = $user->getActiveTimer();

        if (!$activeTimer) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active timer found.',
                ], 400);
            }
            return redirect()->route('time-tracking.index')
                ->with('error', 'No active timer found.');
        }

        $this->timeTrackingService->stopTimer($activeTimer, $user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()->route('time-tracking.index')
            ->with('success', 'Timer stopped successfully.');
    }

    public function createManual(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'description' => 'nullable|string',
            'is_billable' => 'nullable|boolean',
        ]);

        $task = Task::findOrFail($validated['task_id']);
        
        // Validate task is in current workspace
        if ($task->workspace_id !== $workspaceId) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found in current workspace.',
            ], 404);
        }
        
        // Guests can only track time on tasks assigned to them
        if ($user->isGuestInWorkspace($workspaceId)) {
            if (!$task->assignees->contains($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only track time on tasks assigned to you.',
                ], 403);
            }
        }
        
        $this->authorize('trackTime', $task);

        $timeEntry = $this->timeTrackingService->createManualEntry(
            $task,
            auth()->user(),
            new \DateTime($validated['start_time']),
            new \DateTime($validated['end_time']),
            $validated['description'] ?? null,
            false
        );

        return response()->json([
            'success' => true,
            'time_entry' => $timeEntry,
        ]);
    }
}
