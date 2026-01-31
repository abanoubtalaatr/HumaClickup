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
        $endDate = $this->getEndDateForPeriod($period);
        
        // Get all tracks for filter
        $tracks = \App\Models\Track::where('workspace_id', $workspaceId)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // Get only guests created by this member
        $guestsQuery = $workspace->users()
            ->wherePivot('role', 'guest')
            ->wherePivot('created_by_user_id', $member->id);
        
        // Apply track filter if provided
        $trackFilter = $request->get('track_id');
        if ($trackFilter) {
            $guestsQuery->wherePivot('track_id', $trackFilter);
        }
        
        $guests = $guestsQuery->get();

        // Get time tracking data for each guest
        $guestTimeData = $guests->map(function ($guest) use ($workspaceId, $startDate, $endDate) {
            $timeEntries = \App\Models\TimeEntry::where('workspace_id', $workspaceId)
                ->where('user_id', $guest->id)
                ->whereNotNull('end_time')
                ->where('start_time', '>=', $startDate)
                ->where('start_time', '<=', $endDate)
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

        // Apply sorting filter
        $sortFilter = $request->get('sort', 'all');
        if ($sortFilter === 'most') {
            // Sort by total duration descending, but filter out zero entries first
            $guestTimeData = $guestTimeData->filter(function ($data) {
                return $data['total_duration'] > 0;
            })->sortByDesc('total_duration')->values();
        } elseif ($sortFilter === 'lower') {
            // Sort by total duration ascending, but filter out zero entries first
            $guestTimeData = $guestTimeData->filter(function ($data) {
                return $data['total_duration'] > 0;
            })->sortBy('total_duration')->values();
        } elseif ($sortFilter === 'never') {
            // Show only users with no time entries
            $guestTimeData = $guestTimeData->filter(function ($data) {
                return $data['entries_count'] === 0;
            })->values();
        }

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
            'member',
            'sortFilter',
            'tracks',
            'trackFilter'
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
        $endDate = $this->getEndDateForPeriod($period);
        
        // Get all tracks for filter
        $tracks = \App\Models\Track::where('workspace_id', $workspaceId)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // Get all guests in the workspace
        $guestsQuery = $workspace->users()
            ->wherePivot('role', 'guest');
        
        // Apply track filter if provided
        $trackFilter = $request->get('track_id');
        if ($trackFilter) {
            $guestsQuery->wherePivot('track_id', $trackFilter);
        }
        
        $guests = $guestsQuery->get();

        // Get time tracking data for each guest
        $guestTimeData = $guests->map(function ($guest) use ($workspaceId, $startDate, $endDate) {
            $timeEntries = \App\Models\TimeEntry::where('workspace_id', $workspaceId)
                ->where('user_id', $guest->id)
                ->whereNotNull('end_time')
                ->where('start_time', '>=', $startDate)
                ->where('start_time', '<=', $endDate)
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

        // Apply sorting filter
        $sortFilter = $request->get('sort', 'all');
        if ($sortFilter === 'most') {
            // Sort by total duration descending, but filter out zero entries first
            $guestTimeData = $guestTimeData->filter(function ($data) {
                return $data['total_duration'] > 0;
            })->sortByDesc('total_duration')->values();
        } elseif ($sortFilter === 'lower') {
            // Sort by total duration ascending, but filter out zero entries first
            $guestTimeData = $guestTimeData->filter(function ($data) {
                return $data['total_duration'] > 0;
            })->sortBy('total_duration')->values();
        } elseif ($sortFilter === 'never') {
            // Show only users with no time entries
            $guestTimeData = $guestTimeData->filter(function ($data) {
                return $data['entries_count'] === 0;
            })->values();
        }

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
            'totalEntries',
            'sortFilter',
            'tracks',
            'trackFilter',
            'workspaceId'
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
     * Get end date based on selected period
     */
    private function getEndDateForPeriod(string $period)
    {
        return match($period) {
            'day' => now()->endOfDay(),
            'week' => now()->endOfWeek(),
            '2weeks' => now()->endOfDay(),
            '3weeks' => now()->endOfDay(),
            '4weeks' => now()->endOfDay(),
            'month' => now()->endOfMonth(),
            default => now()->endOfWeek(),
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
        // if ($task->workspace_id !== $workspaceId) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Task not found in current workspace.',
        //     ], 404);
        // }
        
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
        // if ($task->workspace_id !== $workspaceId) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Task not found in current workspace.',
        //     ], 404);
        // }
        
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

    /**
     * Check if the current user can edit the given time entry.
     * Owner/Admin: any entry in workspace. Member: entries of their guests. Otherwise: own entries only.
     */
    private function canEditTimeEntry(\App\Models\TimeEntry $timeEntry, int $workspaceId, $user): bool
    {
        if ($timeEntry->workspace_id != $workspaceId) {
            return false;
        }
        if ($user->isAdminInWorkspace($workspaceId)) {
            return true;
        }
        if ($user->isMemberOnlyInWorkspace($workspaceId)) {
            $workspace = \App\Models\Workspace::find($workspaceId);
            $guestPivot = $workspace->users()->where('user_id', $timeEntry->user_id)->wherePivot('role', 'guest')->first();
            return $guestPivot && (int) $guestPivot->pivot->created_by_user_id === (int) $user->id;
        }
        return (int) $timeEntry->user_id === (int) $user->id;
    }

    public function edit(\App\Models\TimeEntry $timeEntry)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if (!$this->canEditTimeEntry($timeEntry, $workspaceId, $user)) {
            abort(403, 'You do not have permission to edit this time entry.');
        }

        // Get tasks for the edit form (for the entry owner's context when admin/member edits)
        $entryOwner = \App\Models\User::find($timeEntry->user_id);
        $tasks = $this->getTasksForTimeTracking($workspaceId, $entryOwner ?? $user);

        return view('time-tracking.edit', compact('timeEntry', 'tasks'));
    }

    public function update(Request $request, \App\Models\TimeEntry $timeEntry)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if (!$this->canEditTimeEntry($timeEntry, $workspaceId, $user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this time entry.',
                ], 403);
            }
            abort(403, 'You do not have permission to update this time entry.');
        }

        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'description' => 'nullable|string',
            'is_billable' => 'nullable|boolean',
        ]);

        $task = Task::findOrFail($validated['task_id']);
        $entryOwnerId = (int) $timeEntry->user_id;

        // Entry owner (the user who logged the time) must have task assigned when they are a guest
        $owner = \App\Models\User::find($entryOwnerId);
        if ($owner && $owner->isGuestInWorkspace($workspaceId)) {
            if (!$task->assignees->contains($entryOwnerId)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Time can only be logged on tasks assigned to the entry owner.',
                    ], 403);
                }
                return back()->withErrors([
                    'task_id' => 'Time can only be logged on tasks assigned to the entry owner.'
                ])->withInput();
            }
        }

        try {
            $updatedEntry = $this->timeTrackingService->updateEntry(
                $timeEntry,
                [
                    'task_id' => $validated['task_id'],
                    'start_time' => new \DateTime($validated['start_time']),
                    'end_time' => new \DateTime($validated['end_time']),
                    'description' => $validated['description'] ?? null,
                    'is_billable' => $validated['is_billable'] ?? false,
                ],
                $user
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'time_entry' => $updatedEntry,
                ]);
            }

            return redirect()->route('time-tracking.index')
                ->with('success', 'Time entry updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
