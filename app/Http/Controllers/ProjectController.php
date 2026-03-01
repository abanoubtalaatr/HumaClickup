<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Workspace;
use App\Models\User;
use App\Models\Track;
use App\Models\Task;
use App\Services\ProjectPlanningService;
use App\Services\TaskService;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    protected ProjectPlanningService $planningService;

    public function __construct(ProjectPlanningService $planningService)
    {
        $this->planningService = $planningService;
    }

    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        
        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }

        $user = auth()->user();
        $tester = $user->hasTestingTrackInWorkspace($workspaceId);

        // Guests can only see projects they're assigned to: have tasks assigned OR are project guest members
        if ($user->isGuestInWorkspace($workspaceId) && !$tester) {
            $projects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->where(function ($query) use ($user) {
                    $query->whereHas('tasks', function ($q) use ($user) {
                        $q->whereHas('assignees', fn($aq) => $aq->where('user_id', $user->id));
                    })
                    ->orWhereHas('guests', fn($q) => $q->where('user_id', $user->id));
                })
                ->withCount([
                    'tasks' => function ($query) use ($user) {
                        $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
                    },
                    'tasks as bugs_count' => fn($q) => $q->where('type', 'bug'),
                    'comments',
                ])
                ->with(['space', 'createdBy'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }
        // Testing-track guests see projects they're assigned to: project_testers, project_members (tester), or tasks
        elseif ($tester && $user->hasTestingTrackInWorkspace($workspaceId) && $user->isGuestInWorkspace($workspaceId)) {
            $projects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->where(function ($query) use ($user) {
                    $query->whereHas('testers', fn($q) => $q->where('tester_id', $user->id))
                        ->orWhereHas('projectMembers', fn($q) => $q->where('role', 'tester')->where('user_id', $user->id))
                        ->orWhereHas('tasks', function ($q) use ($user) {
                            $q->whereHas('assignees', fn($aq) => $aq->where('user_id', $user->id));
                        });
                })
                ->withCount([
                    'tasks',
                    'tasks as bugs_count' => fn($q) => $q->where('type', 'bug'),
                    'comments',
                ])
                ->with(['space', 'createdBy'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }
        // Admin, Owner, or Testing track members can see all projects
        elseif ($user->isAdminInWorkspace($workspaceId) 
            || $user->isOwnerInWorkspace($workspaceId) 
            || $user->hasTestingTrackInWorkspace($workspaceId)) {
            $projects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->withCount([
                    'tasks',
                    'tasks as bugs_count' => fn($q) => $q->where('type', 'bug'),
                    'comments',
                ])
                ->with(['space', 'createdBy'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }
        // Regular members can only see projects they created
        else {
            $projects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->where('created_by_user_id', $user->id)
                ->withCount([
                    'tasks',
                    'tasks as bugs_count' => fn($q) => $q->where('type', 'bug'),
                    'comments',
                ])
                ->with(['space', 'createdBy'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }
        
        $isGuest = $user->isGuestInWorkspace($workspaceId);

        return view('projects.index', compact('projects', 'isGuest'));
    }

    public function create()
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        // Only members and admins can create projects
        if (!$user->canCreateInWorkspace($workspaceId)) {
            abort(403, 'Guests cannot create projects.');
        }
        
        $workspace = Workspace::find($workspaceId);
        $spaces = $workspace?->spaces ?? collect();

        // Get guests based on user role:
        // - Members: only their created guests
        // - Admin/Owner: all guests in workspace
        if ($user->isMemberOnlyInWorkspace($workspaceId)) {
            $guests = $workspace->guestsCreatedBy($user->id)->get();
        } else {
            $guests = $workspace->users()
                ->wherePivot('role', 'guest')
                ->get();
        }

        // Get groups based on user role:
        // - Members: only their created groups
        // - Admin/Owner: all groups in workspace
        if ($user->isMemberOnlyInWorkspace($workspaceId)) {
            $groups = \App\Models\Group::where('workspace_id', $workspaceId)
                ->where('created_by_user_id', $user->id)
                ->with('guests')
                ->get();
        } else {
            $groups = \App\Models\Group::where('workspace_id', $workspaceId)
                ->with('guests')
                ->get();
        }

        // Get all tracks for track selection
        $tracks = Track::where('workspace_id', $workspaceId)->get();

        // Use wizard view for better UX
        return view('projects.create-wizard', compact('spaces', 'guests', 'groups', 'tracks'));
    }

    public function store(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        
        // Only members and admins can create projects
        if (!auth()->user()->canCreateInWorkspace($workspaceId)) {
            abort(403, 'Guests cannot create projects.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'space_id' => 'nullable|exists:spaces,id',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string',
            'start_date' => 'required|date',
            'total_days' => 'required|integer|min:1|max:365',
            'exclude_weekends' => 'boolean',
            'min_task_hours' => 'nullable|numeric|min:1|max:24',
            'weekly_hours_target' => 'nullable|numeric|min:1',
            'bug_time_allocation_percentage' => 'nullable|numeric|min:0|max:50',
            'guest_members' => 'required|array|min:1',
            'guest_members.*.user_id' => 'required|exists:users,id',
            'guest_members.*.track_id' => 'nullable|exists:tracks,id',
        ]);

        $validated['workspace_id'] = $workspaceId;
        $validated['created_by_user_id'] = auth()->id();
        $validated['progress'] = 0;
        $validated['is_archived'] = false;

        // Create project
        $project = Project::create([
            'workspace_id' => $validated['workspace_id'],
            'space_id' => $validated['space_id'] ?? null,
            'created_by_user_id' => $validated['created_by_user_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#6366f1',
            'icon' => $validated['icon'] ?? '📁',
            'progress' => 0,
            'is_archived' => false,
        ]);

        // Create default statuses for software development workflow
        $defaultStatuses = [
            ['name' => 'To Do', 'color' => '#94a3b8', 'type' => 'todo', 'order' => 0, 'is_default' => true, 'progress_contribution' => 0],
            ['name' => 'In Progress', 'color' => '#3b82f6', 'type' => 'in_progress', 'order' => 1, 'progress_contribution' => 25],
            ['name' => 'In Review', 'color' => '#f59e0b', 'type' => 'in_progress', 'order' => 2, 'progress_contribution' => 50],
            ['name' => 'Retest', 'color' => '#ec4899', 'type' => 'in_progress', 'order' => 3, 'progress_contribution' => 70],
            ['name' => 'Blocked', 'color' => '#ef4444', 'type' => 'in_progress', 'order' => 4, 'progress_contribution' => 0],
            ['name' => 'Closed', 'color' => '#10b981', 'type' => 'done', 'order' => 5, 'progress_contribution' => 100],
        ];

        foreach ($defaultStatuses as $status) {
            $project->customStatuses()->create($status);
        }

        // Initialize project planning with guests and dates
        try {
            $this->planningService->initializeProject(
                $project,
                $validated['guest_members'],
                [
                    'start_date' => $validated['start_date'],
                    'total_days' => $validated['total_days'],
                    'exclude_weekends' => $validated['exclude_weekends'] ?? true,
                    'min_task_hours' => $validated['min_task_hours'] ?? 6,
                    'bug_time_allocation_percentage' => $validated['bug_time_allocation_percentage'] ?? 20,
                    'weekly_hours_target' => $validated['weekly_hours_target'] ?? 30,
                ]
            );

            return redirect()->route('projects.show', $project)
                ->with('success', 'Project created successfully! You must create ' . $project->required_main_tasks_count . ' main tasks before starting.');
        } catch (\Exception $e) {
            // Rollback project if planning fails
            $project->delete();
            
            return back()
                ->withInput()
                ->with('error', 'Failed to initialize project: ' . $e->getMessage());
        }
    }

    /**
     * Store project with all main tasks and subtasks in one transaction.
     * This is the wizard endpoint.
     */
    public function storeWithTasks(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        // Only members and admins can create projects
        if (!$user->canCreateInWorkspace($workspaceId)) {
            return response()->json([
                'success' => false,
                'message' => 'Guests cannot create projects.'
            ], 403);
        }

        // Validate the entire payload
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'total_days' => 'required|integer|min:1|max:365',
            'exclude_weekends' => 'boolean',
            'guest_members' => 'required|array|min:1',
            'guest_members.*.user_id' => 'required|exists:users,id',
            'guest_members.*.track_id' => 'nullable|exists:tracks,id',
            'main_tasks' => 'required|array|min:1',
            'main_tasks.*.title' => 'required|string|max:255',
            'main_tasks.*.description' => 'nullable|string',
            'main_tasks.*.guest_user_id' => 'required|exists:users,id',
            'main_tasks.*.day_number' => 'required|integer|min:1',
            'main_tasks.*.estimated_hours' => 'required|numeric|min:6',
            'main_tasks.*.subtasks' => 'nullable|array',
            'main_tasks.*.subtasks.*.title' => 'required|string|max:255',
            'main_tasks.*.subtasks.*.description' => 'nullable|string',
            'main_tasks.*.subtasks.*.estimated_hours' => 'required|numeric|min:0.5',
        ]);

        try {
            return DB::transaction(function () use ($validated, $workspaceId, $user) {
                // 1. Create project
                $project = Project::create([
                    'workspace_id' => $workspaceId,
                    'created_by_user_id' => $user->id,
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'color' => '#6366f1',
                    'icon' => '📁',
                    'progress' => 0,
                    'is_archived' => false,
                ]);

                // 2. Create default statuses
                $defaultStatuses = [
                    ['name' => 'To Do', 'color' => '#94a3b8', 'type' => 'todo', 'order' => 0, 'is_default' => true, 'progress_contribution' => 0],
                    ['name' => 'In Progress', 'color' => '#3b82f6', 'type' => 'in_progress', 'order' => 1, 'progress_contribution' => 25],
                    ['name' => 'Done', 'color' => '#10b981', 'type' => 'done', 'order' => 2, 'progress_contribution' => 100],
                    ['name' => 'In Review', 'color' => '#f59e0b', 'type' => 'in_progress', 'order' => 3, 'progress_contribution' => 50],
                    ['name' => 'Retest', 'color' => '#ec4899', 'type' => 'in_progress', 'order' => 4, 'progress_contribution' => 70],
                    ['name' => 'Blocked', 'color' => '#ef4444', 'type' => 'in_progress', 'order' => 5, 'progress_contribution' => 0],
                    ['name' => 'Closed', 'color' => '#22c55e', 'type' => 'done', 'order' => 6, 'progress_contribution' => 100],
                ];

                foreach ($defaultStatuses as $statusData) {
                    $project->customStatuses()->create($statusData);
                }

                $todoStatus = $project->customStatuses()->where('type', 'todo')->first();

                // 3. Initialize project planning
                $this->planningService->initializeProject(
                    $project,
                    $validated['guest_members'],
                    [
                        'start_date' => $validated['start_date'],
                        'total_days' => $validated['total_days'],
                        'exclude_weekends' => $validated['exclude_weekends'] ?? true,
                        'min_task_hours' => 6,
                        'bug_time_allocation_percentage' => 20,
                        'weekly_hours_target' => 30,
                    ]
                );

                // 4. Create all main tasks
                $taskService = app(TaskService::class);
                $startDate = \Carbon\Carbon::parse($validated['start_date']);

                foreach ($validated['main_tasks'] as $taskData) {
                    // Calculate task date (skip weekends)
                    $taskDate = $this->calculateTaskDate($startDate, $taskData['day_number'] - 1, $validated['exclude_weekends'] ?? true);

                    // Create main task
                    $mainTask = Task::create([
                        'workspace_id' => $workspaceId,
                        'project_id' => $project->id,
                        'creator_id' => $user->id,
                        'title' => $taskData['title'],
                        'description' => $taskData['description'] ?? null,
                        'estimated_time' => $taskData['estimated_hours'],
                        'status_id' => $todoStatus->id,
                        'is_main_task' => 'yes',
                        'assigned_date' => $taskDate,
                        'due_date' => $taskDate->copy()->setTime(23, 0, 0), // Due at 11 PM
                        'priority' => 'high',
                    ]);

                    // Assign to guest
                    $mainTask->assignees()->attach($taskData['guest_user_id']);

                    // Send notification to assigned guest
                    $assignedGuest = User::find($taskData['guest_user_id']);
                    if ($assignedGuest) {
                        $assignedGuest->notify(new TaskAssignedNotification($mainTask, $user));
                    }

                    // Calculate bug time limit
                    $mainTask->update([
                        'bug_time_limit' => $mainTask->calculateBugTimeLimit(),
                    ]);

                    // Create subtasks if any
                    if (!empty($taskData['subtasks'])) {
                        foreach ($taskData['subtasks'] as $subtaskData) {
                            $subtask = Task::create([
                                'workspace_id' => $workspaceId,
                                'project_id' => $project->id,
                                'parent_id' => $mainTask->id,
                                'creator_id' => $user->id,
                                'title' => $subtaskData['title'],
                                'description' => $subtaskData['description'] ?? null,
                                'estimated_time' => $subtaskData['estimated_hours'],
                                'status_id' => $todoStatus->id,
                                'is_main_task' => 'no',
                                'assigned_date' => $taskDate,
                                'due_date' => $taskDate->copy()->setTime(23, 0, 0),
                            ]);

                            // Assign subtask to same guest
                            $subtask->assignees()->attach($taskData['guest_user_id']);
                        }
                    }
                }

                // 5. Update main tasks count
                $this->planningService->updateMainTasksStatus($project);

                return response()->json([
                    'success' => true,
                    'message' => 'Project created successfully with all tasks!',
                    'redirect' => route('projects.show', $project),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate task date based on day number, excluding weekends.
     */
    private function calculateTaskDate(\Carbon\Carbon $startDate, int $dayIndex, bool $excludeWeekends): \Carbon\Carbon
    {
        if (!$excludeWeekends) {
            return $startDate->copy()->addDays($dayIndex);
        }

        $currentDate = $startDate->copy();
        $daysAdded = 0;

        while ($daysAdded < $dayIndex) {
            $currentDate->addDay();
            // Skip Friday (5) and Saturday (6)
            if (!in_array($currentDate->dayOfWeek, [5, 6])) {
                $daysAdded++;
            }
        }

        // Add one more day for the actual task date
        while (in_array($currentDate->dayOfWeek, [5, 6])) {
            $currentDate->addDay();
        }

        return $currentDate;
    }

    public function show(Request $request, Project $project)
    {
        
        
        // Ensure project belongs to current workspace
        $workspaceId = session('current_workspace_id');
        
        // if ($project->workspace_id != $workspaceId) {
        //     abort(404, 'Project not found.');
        // }
        
        // $this->authorize('view', $project);

        $project->loadCount([
            'tasks',
            'tasks as completed_tasks_count' => fn($q) => $q->whereHas('status', fn($sq) => $sq->where('type', 'done'))
        ]);
        
        // Eager load relationships on already-instantiated model
        $project->load([
            'projectMembers.user',
            'projectMembers.track',
            'testers.tester',        // project_testers table (the actual assigned testers)
            'createdBy',
        ]);

        // Calculate time logged
        $timeEntries = $project->tasks()
            ->withSum('timeEntries', 'duration')
            ->get();
        $totalSeconds = $timeEntries->sum('time_entries_sum_duration') ?? 0;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $project->time_logged_formatted = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        // Ensure project belongs to current workspace
        $workspaceId = session('current_workspace_id');
        if ($project->workspace_id !== $workspaceId) {
            abort(404, 'Project not found.');
        }
        
        $this->authorize('update', $project);
        
        $workspace = Workspace::find($workspaceId);
        $spaces = $workspace?->spaces ?? collect();
        $user = auth()->user();

        // Get guests based on user role
        if ($user->isMemberOnlyInWorkspace($workspaceId)) {
            $guests = $workspace->guestsCreatedBy($user->id)->get();
        } else {
            $guests = $workspace->users()
                ->wherePivot('role', 'guest')
                ->get();
        }

        // Get groups based on user role
        if ($user->isMemberOnlyInWorkspace($workspaceId)) {
            $groups = \App\Models\Group::where('workspace_id', $workspaceId)
                ->where('created_by_user_id', $user->id)
                ->with('guests')
                ->get();
        } else {
            $groups = \App\Models\Group::where('workspace_id', $workspaceId)
                ->with('guests')
                ->get();
        }

        // Get all tracks
        $tracks = Track::where('workspace_id', $workspaceId)->get();

        // Load existing project guests
        $projectGuests = $project->guests()->with('user', 'track')->get()->map(function ($pm) {
            return [
                'user_id' => $pm->user_id,
                'name' => $pm->user->name,
                'track_id' => $pm->track_id,
            ];
        });

        // Load existing main tasks with subtasks, grouped by guest
        $existingTasks = $project->tasks()
            ->where('is_main_task', 'yes')
            ->with(['subtasks' => function ($q) {
                $q->orderBy('id');
            }, 'assignees'])
            ->orderBy('assigned_date')
            ->orderBy('id')
            ->get()
            ->map(function ($task) use ($project) {
                $assignee = $task->assignees->first();
                
                // Calculate working day number from project start
                $dayNumber = 1;
                if ($task->assigned_date && $project->start_date) {
                    $current = $project->start_date->copy();
                    $workingDay = 0;
                    while ($current->lte($task->assigned_date)) {
                        if (!in_array($current->dayOfWeek, [5, 6])) {
                            $workingDay++;
                        }
                        if ($current->isSameDay($task->assigned_date)) break;
                        $current->addDay();
                    }
                    $dayNumber = max(1, $workingDay);
                }
                
                return [
                    'db_id' => $task->id,
                    'guest_user_id' => $assignee ? $assignee->id : null,
                    'guest_name' => $assignee ? $assignee->name : 'Unassigned',
                    'track_id' => null,
                    'day_number' => $dayNumber,
                    'title' => $task->title,
                    'description' => $task->description ?? '',
                    'estimated_hours' => (float) ($task->estimated_time ?? 6),
                    'status' => $task->status?->name ?? 'To Do',
                    'subtasks' => $task->subtasks->map(function ($st) {
                        return [
                            'db_id' => $st->id,
                            'title' => $st->title,
                            'description' => $st->description ?? '',
                            'estimated_hours' => (float) ($st->estimated_time ?? 0),
                        ];
                    })->values()->toArray(),
                ];
            });

        return view('projects.edit-wizard', compact(
            'project', 'spaces', 'guests', 'groups', 'tracks',
            'projectGuests', 'existingTasks'
        ));
    }

    public function update(Request $request, Project $project)
    {
        // Ensure project belongs to current workspace
        $workspaceId = session('current_workspace_id');
        if ($project->workspace_id !== $workspaceId) {
            abort(404, 'Project not found.');
        }
        
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'space_id' => 'nullable|exists:spaces,id',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Update project with all main tasks and subtasks in one transaction.
     * This is the edit wizard endpoint.
     */
    public function updateWithTasks(Request $request, Project $project)
    {
        $workspaceId = session('current_workspace_id');
        if ($project->workspace_id !== $workspaceId) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $this->authorize('update', $project);
        $user = auth()->user();

        // Validate the entire payload
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'total_days' => 'required|integer|min:1|max:365',
            'exclude_weekends' => 'boolean',
            'guest_members' => 'required|array|min:1',
            'guest_members.*.user_id' => 'required|exists:users,id',
            'guest_members.*.track_id' => 'nullable|exists:tracks,id',
            'main_tasks' => 'required|array|min:1',
            'main_tasks.*.db_id' => 'nullable|integer',
            'main_tasks.*.title' => 'required|string|max:255',
            'main_tasks.*.description' => 'nullable|string',
            'main_tasks.*.guest_user_id' => 'required|exists:users,id',
            'main_tasks.*.day_number' => 'required|integer|min:1',
            'main_tasks.*.estimated_hours' => 'required|numeric|min:6',
            'main_tasks.*.subtasks' => 'nullable|array',
            'main_tasks.*.subtasks.*.db_id' => 'nullable|integer',
            'main_tasks.*.subtasks.*.title' => 'required|string|max:255',
            'main_tasks.*.subtasks.*.description' => 'nullable|string',
            'main_tasks.*.subtasks.*.estimated_hours' => 'required|numeric|min:0.5',
        ]);

        try {
            return DB::transaction(function () use ($validated, $project, $workspaceId, $user) {
                // 1. Update project basic info
                $project->update([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                ]);

                // 2. Calculate new dates
                $startDate = \Carbon\Carbon::parse($validated['start_date']);
                $excludeWeekends = $validated['exclude_weekends'] ?? true;
                $totalDays = $validated['total_days'];
                $endDate = $this->planningService->calculateEndDate($startDate, $totalDays, $excludeWeekends);
                $workingDays = $this->planningService->calculateWorkingDays($startDate, $endDate, $excludeWeekends);

                // 3. Sync guest members
                $newGuestIds = collect($validated['guest_members'])->pluck('user_id')->toArray();
                $existingGuestIds = $project->guests()->pluck('user_id')->toArray();

                // Remove guests that were deselected (and their tasks)
                $removedGuestIds = array_diff($existingGuestIds, $newGuestIds);
                if (!empty($removedGuestIds)) {
                    // Delete tasks assigned to removed guests
                    $tasksToDelete = $project->tasks()
                        ->whereHas('assignees', function ($q) use ($removedGuestIds) {
                            $q->whereIn('user_id', $removedGuestIds);
                        })->get();
                    
                    foreach ($tasksToDelete as $task) {
                        // Delete subtasks first
                        $task->subtasks()->delete();
                        $task->assignees()->detach();
                        $task->delete();
                    }

                    // Remove project member records
                    $project->projectMembers()
                        ->where('role', 'guest')
                        ->whereIn('user_id', $removedGuestIds)
                        ->delete();
                }

                // Add new guests
                $addedGuestIds = array_diff($newGuestIds, $existingGuestIds);
                foreach ($validated['guest_members'] as $guestData) {
                    if (in_array($guestData['user_id'], $addedGuestIds)) {
                        $guestUser = User::findOrFail($guestData['user_id']);
                        $track = isset($guestData['track_id']) ? \App\Models\Track::find($guestData['track_id']) : null;
                        $project->addGuestMember($guestUser, $track);
                    }
                }

                // 4. Update project planning fields
                $requiredMainTasks = count($newGuestIds) * $workingDays;
                $project->update([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_days' => $totalDays,
                    'working_days' => $workingDays,
                    'exclude_weekends' => $excludeWeekends,
                    'required_main_tasks_count' => $requiredMainTasks,
                    'min_task_hours' => 6,
                    'bug_time_allocation_percentage' => 20,
                    'weekly_hours_target' => 30,
                ]);

                $todoStatus = $project->customStatuses()->where('type', 'todo')->first();

                // 5. Track which existing task IDs are still in the payload
                $keepTaskDbIds = collect($validated['main_tasks'])
                    ->pluck('db_id')
                    ->filter()
                    ->toArray();

                // 6. Delete tasks that are no longer in the payload (only main tasks that belong to remaining guests)
                $remainingMainTasks = $project->tasks()
                    ->where('is_main_task', 'yes')
                    ->whereHas('assignees', function ($q) use ($newGuestIds) {
                        $q->whereIn('user_id', $newGuestIds);
                    })
                    ->whereNotIn('id', $keepTaskDbIds)
                    ->get();

                foreach ($remainingMainTasks as $task) {
                    $task->subtasks()->delete();
                    $task->assignees()->detach();
                    $task->delete();
                }

                // 7. Create/Update main tasks
                foreach ($validated['main_tasks'] as $taskData) {
                    $taskDate = $this->calculateTaskDate($startDate, $taskData['day_number'] - 1, $excludeWeekends);

                    if (!empty($taskData['db_id'])) {
                        // Update existing task
                        $mainTask = Task::find($taskData['db_id']);
                        if ($mainTask && $mainTask->project_id === $project->id) {
                            $mainTask->update([
                                'title' => $taskData['title'],
                                'description' => $taskData['description'] ?? null,
                                'estimated_time' => $taskData['estimated_hours'],
                                'assigned_date' => $taskDate,
                                'due_date' => $taskDate->copy()->setTime(23, 0, 0),
                            ]);

                            // Update assignee if changed
                            $currentAssignee = $mainTask->assignees()->first();
                            if (!$currentAssignee || $currentAssignee->id !== $taskData['guest_user_id']) {
                                $mainTask->assignees()->sync([$taskData['guest_user_id']]);
                                
                                // Notify new assignee
                                $assignedGuest = User::find($taskData['guest_user_id']);
                                if ($assignedGuest) {
                                    $assignedGuest->notify(new TaskAssignedNotification($mainTask, $user));
                                }
                            }

                            // Update bug time limit
                            $mainTask->update(['bug_time_limit' => $mainTask->calculateBugTimeLimit()]);

                            // Handle subtasks
                            $this->syncSubtasks($mainTask, $taskData['subtasks'] ?? [], $workspaceId, $project, $user, $todoStatus, $taskDate, $taskData['guest_user_id']);
                        }
                    } else {
                        // Create new task
                        $mainTask = Task::create([
                            'workspace_id' => $workspaceId,
                            'project_id' => $project->id,
                            'creator_id' => $user->id,
                            'title' => $taskData['title'],
                            'description' => $taskData['description'] ?? null,
                            'estimated_time' => $taskData['estimated_hours'],
                            'status_id' => $todoStatus?->id,
                            'is_main_task' => 'yes',
                            'assigned_date' => $taskDate,
                            'due_date' => $taskDate->copy()->setTime(23, 0, 0),
                            'priority' => 'high',
                        ]);

                        $mainTask->assignees()->attach($taskData['guest_user_id']);

                        // Notify assigned guest
                        $assignedGuest = User::find($taskData['guest_user_id']);
                        if ($assignedGuest) {
                            $assignedGuest->notify(new TaskAssignedNotification($mainTask, $user));
                        }

                        $mainTask->update(['bug_time_limit' => $mainTask->calculateBugTimeLimit()]);

                        // Create subtasks
                        if (!empty($taskData['subtasks'])) {
                            foreach ($taskData['subtasks'] as $subtaskData) {
                                $subtask = Task::create([
                                    'workspace_id' => $workspaceId,
                                    'project_id' => $project->id,
                                    'parent_id' => $mainTask->id,
                                    'creator_id' => $user->id,
                                    'title' => $subtaskData['title'],
                                    'description' => $subtaskData['description'] ?? null,
                                    'estimated_time' => $subtaskData['estimated_hours'],
                                    'status_id' => $todoStatus?->id,
                                    'is_main_task' => 'no',
                                    'assigned_date' => $taskDate,
                                    'due_date' => $taskDate->copy()->setTime(23, 0, 0),
                                ]);
                                $subtask->assignees()->attach($taskData['guest_user_id']);
                            }
                        }
                    }
                }

                // 8. Update main tasks count
                $this->planningService->updateMainTasksStatus($project);

                return response()->json([
                    'success' => true,
                    'message' => 'Project updated successfully!',
                    'redirect' => route('projects.show', $project),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync subtasks for a main task during edit.
     */
    private function syncSubtasks(Task $mainTask, array $subtasksData, $workspaceId, Project $project, $user, $todoStatus, $taskDate, $guestUserId)
    {
        // Delete subtasks that are no longer in the payload
        $payloadSubtaskIds = collect($subtasksData)->pluck('db_id')->filter()->toArray();
        $mainTask->subtasks()
            ->whereNotIn('id', $payloadSubtaskIds)
            ->each(function ($subtask) {
                $subtask->assignees()->detach();
                $subtask->delete();
            });

        // Create or update subtasks
        foreach ($subtasksData as $subtaskData) {
            if (!empty($subtaskData['db_id'])) {
                // Update existing subtask
                $subtask = Task::find($subtaskData['db_id']);
                if ($subtask && $subtask->parent_id === $mainTask->id) {
                    $subtask->update([
                        'title' => $subtaskData['title'],
                        'description' => $subtaskData['description'] ?? null,
                        'estimated_time' => $subtaskData['estimated_hours'],
                        'assigned_date' => $taskDate,
                        'due_date' => $taskDate->copy()->setTime(23, 0, 0),
                    ]);
                }
            } else {
                // Create new subtask
                $subtask = Task::create([
                    'workspace_id' => $workspaceId,
                    'project_id' => $project->id,
                    'parent_id' => $mainTask->id,
                    'creator_id' => $user->id,
                    'title' => $subtaskData['title'],
                    'description' => $subtaskData['description'] ?? null,
                    'estimated_time' => $subtaskData['estimated_hours'],
                    'status_id' => $todoStatus?->id,
                    'is_main_task' => 'no',
                    'assigned_date' => $taskDate,
                    'due_date' => $taskDate->copy()->setTime(23, 0, 0),
                ]);
                $subtask->assignees()->attach($guestUserId);
            }
        }
    }

    public function destroy(Project $project)
    {
        // Ensure project belongs to current workspace
        $workspaceId = session('current_workspace_id');
        if ($project->workspace_id !== $workspaceId) {
            abort(404, 'Project not found.');
        }
        
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
