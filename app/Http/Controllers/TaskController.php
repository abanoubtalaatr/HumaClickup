<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\CustomStatus;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    public function index(Request $request, ?Project $project = null)
    {
        
        // Default to Kanban view
        return $this->kanban($request, $project);
    }

    public function list(Request $request, ?Project $project = null)
    {
        
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        $isGuest = $user->isGuestInWorkspace($workspaceId);
        $tester = $user->hasTestingTrackInWorkspace($workspaceId);
        // Get all projects for filter based on user role
        if ($isGuest && !$tester) {
            $allProjects = Project::where('workspace_id', $workspaceId)
                ->whereHas('tasks', function ($query) use ($user) {
                    $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
                })
                ->get();
        } elseif ($user->isAdminInWorkspace($workspaceId) 
            || $user->isOwnerInWorkspace($workspaceId) 
            || $user->hasTestingTrackInWorkspace($workspaceId)) {
            // Admin, Owner, or Testing track members can see all projects
            $allProjects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->get();
        } else {
            // Regular members can only see their own projects
            $allProjects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->where('created_by_user_id', $user->id)
                ->get();
        }
        
        // Determine which project to show
        if (!$project) {
            $selectedProjectId = $request->get('project_id');
            if ($selectedProjectId) {
                $project = Project::where('workspace_id', $workspaceId)->find($selectedProjectId);
            } else {
                $project = $allProjects->first();
            }
        } else {
            // $this->authorize('view', $project);
        }
        
        $query = Task::where('workspace_id', $workspaceId);
        
        // Apply filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        } elseif ($project) {
            $query->where('project_id', $project->id);
        }
        
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        
        if ($request->filled('assignee_id')) {
            $query->whereHas('assignees', fn($q) => $q->where('user_id', $request->assignee_id));
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Guests can only see tasks assigned to them
        if ($isGuest) {
            $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
        }

        $tasks = $query->with(['assignees', 'status', 'tags', 'project'])
            ->orderBy('position')
            ->get();

        $statuses = $project ? $project->customStatuses()->orderBy('order')->get() : collect();
        $assignees = $this->getAssignableUsersForCreation($workspaceId);
        $tags = \App\Models\Tag::where('workspace_id', $workspaceId)->get();
        $projects = $allProjects;
        
        // Get sprints for the workspace (all statuses for filtering)
        $sprints = \App\Models\Sprint::where('workspace_id', $workspaceId)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('tasks.list', compact('tasks', 'project', 'projects', 'statuses', 'assignees', 'tags', 'sprints', 'isGuest'));
    }

    public function kanban(Request $request, ?Project $project = null)
    {
        
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        $isGuest = $user->isGuestInWorkspace($workspaceId) ;
        $tester = $user->hasTestingTrackInWorkspace($workspaceId);
        
        // Get all projects for filter dropdown based on user role
        if($isGuest && $user->hasTestingTrackInWorkspace($workspaceId)){
            $allProjects = Project::where('workspace_id', $workspaceId)
            ->where('is_archived', false)
            ->get();
            
        }
        elseif ($isGuest ) {
            $allProjects = Project::where('workspace_id', $workspaceId)
                ->whereHas('tasks', function ($query) use ($user) {
                    $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
                })
                ->get();
        } elseif ($user->isAdminInWorkspace($workspaceId) 
            || $user->isOwnerInWorkspace($workspaceId) 
            || $user->hasTestingTrackInWorkspace($workspaceId)) {
            // Admin, Owner, or Testing track members can see all projects
            $allProjects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->get();
        } else {
            // Regular members can only see their own projects
            $allProjects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->where('created_by_user_id', $user->id)
                ->get();
        }
        
        
        if ($project) {
            
            // $this->authorize('view', $project);
            $statuses = $project->customStatuses()->orderBy('order')->get();
            
        } else {
            // If no project specified, check if there's a project_id in request or use first project
            $selectedProjectId = $request->get('project_id');
            
            if ($selectedProjectId) {
                $project = Project::where('workspace_id', $workspaceId)->find($selectedProjectId);
            } else {
                $project = null;
            }
            
            if ($project) {
                
                $statuses = $project->customStatuses()->orderBy('order')->get();
            } else {
                $statuses = collect();
                
            }
        }
        

        // Load tasks for each status
        foreach ($statuses as $status) {
            $taskQuery = Task::where('status_id', $status->id)
                ->where('workspace_id', $workspaceId);
            
            // Apply project filter
            if ($request->filled('project_id')) {
                $taskQuery->where('project_id', $request->project_id);
                
            } elseif ($project) {
                $taskQuery->where('project_id', $project->id);
            }
            
            // Apply sprint filter
            if ($request->filled('sprint_id')) {
                $taskQuery->where('sprint_id', $request->sprint_id);
            }
            
            // Apply assignee filter
            if ($request->filled('assignee_id')) {
                $taskQuery->whereHas('assignees', fn($q) => $q->where('user_id', $request->assignee_id));
            }
            
            // Apply priority filter
            if ($request->filled('priority')) {
                $taskQuery->where('priority', $request->priority);
            }
            
            // Guests can only see tasks assigned to them
            if ($isGuest && !$tester) {
                $taskQuery->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
            }
            
            $status->tasks = $taskQuery
                ->with(['assignees', 'tags'])
                ->withCount(['comments', 'attachments', 'subtasks'])
                ->orderBy('position')
                ->get();
        }

        // Get assignable users based on current user's role
        $assignees = $this->getAssignableUsersForCreation($workspaceId);
        $tags = \App\Models\Tag::where('workspace_id', $workspaceId)->get();
        $users = $assignees;
        
        // Use the projects we already fetched
        $projects = $allProjects;
        
        // Get sprints for the workspace (all statuses for filtering)
        $sprints = \App\Models\Sprint::where('workspace_id', $workspaceId)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('tasks.kanban', compact('statuses', 'project', 'projects', 'assignees', 'tags', 'users', 'sprints', 'isGuest','tester'));
    }

    /**
     * Get users that can be assigned to tasks based on current user's role
     */
    private function getAssignableUsersForCreation(int $workspaceId): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        
        // Guests cannot assign tasks
        if ($user->isGuestInWorkspace($workspaceId)) {
            return collect();
        }
        
        // Testing track members can assign to any user in workspace (like admins)
        if ($user->hasTestingTrackInWorkspace($workspaceId)) {
            return User::whereHas('workspaces', fn($q) => $q->where('workspaces.id', $workspaceId))->get();
        }
        
        // Regular members can assign to themselves and guests they created
        if ($user->isMemberOnlyInWorkspace($workspaceId)) {
            $guests = $user->getCreatedGuestsInWorkspace($workspaceId);
            // Add the member themselves to the assignable users list
            return $guests->push($user)->unique('id');
        }
        
        // Admin/Owner can assign to any user in workspace
        return User::whereHas('workspaces', fn($q) => $q->where('workspaces.id', $workspaceId))->get();
    }

    /**
     * API endpoint to get assignable users for task creation
     */
    public function getAssignableUsers(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $users = $this->getAssignableUsersForCreation($workspaceId);
        
        return response()->json($users->map(function ($user) use ($workspaceId) {
            $track = $user->getTrackInWorkspace($workspaceId);
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'track' => $track?->name,
                'track_color' => $track?->color,
            ];
        }));
    }

    public function create(Request $request, ?Project $project = null)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        if ($project) {
            $this->authorize('create', [Task::class, $workspaceId, $project->id]);
        } else {
            $this->authorize('create', [Task::class, $workspaceId]);
        }

        // Filter projects based on user role
        if ($user->isAdminInWorkspace($workspaceId) 
            || $user->isOwnerInWorkspace($workspaceId) 
            || $user->hasTestingTrackInWorkspace($workspaceId)) {
            $projects = Project::where('workspace_id', $workspaceId)->get();
        } else {
            // Regular members can only see their own projects
            $projects = Project::where('workspace_id', $workspaceId)
                ->where('created_by_user_id', $user->id)
                ->get();
        }
        $statuses = $project?->customStatuses ?? collect();
        $users = $this->getAssignableUsersForCreation($workspaceId);
        $tags = \App\Models\Tag::where('workspace_id', $workspaceId)->get();
        
        // Get active sprints for the workspace
        $sprints = \App\Models\Sprint::where('workspace_id', $workspaceId)
            ->whereIn('status', ['planning', 'active'])
            ->orderBy('start_date', 'desc')
            ->get();

        return view('tasks.create', compact('project', 'projects', 'statuses', 'users', 'tags', 'sprints'));
    }

    public function store(Request $request, ?Project $project = null)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => $project ? 'nullable' : 'required|exists:projects,id',
            'status_id' => 'nullable|exists:custom_statuses,id',
            'priority' => 'nullable|in:urgent,high,normal,low,none',
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'estimated_time' => 'nullable|integer',
            'sprint_id' => 'nullable|exists:sprints,id',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $project = $project ?? Project::find($validated['project_id']);
        // $this->authorize('create', [Task::class, $workspaceId, $project->id]);

        // Validate that assignees are valid for the current user
        if (!empty($validated['assignee_ids'])) {
            $allowedAssignees = $this->getAssignableUsersForCreation($workspaceId)->pluck('id')->toArray();
            foreach ($validated['assignee_ids'] as $assigneeId) {
                if (!in_array($assigneeId, $allowedAssignees)) {
                    if ($request->expectsJson() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You can only assign tasks to users you have permission to manage.'
                        ], 403);
                    }
                    return back()->with('error', 'You can only assign tasks to users you have permission to manage.');
                }
            }
        }

        if (isset($validated['estimated_time'])) {
            $validated['estimated_time'] = $validated['estimated_time']; // Keep as minutes
        }

        $task = $this->taskService->create($validated, auth()->user(), $project);

        // Return JSON if requested via AJAX
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task,
                'message' => 'Task created successfully.'
            ]);
        }

        // Use project-specific route if project exists, otherwise use standalone route
        if ($project) {
            return redirect()->route('projects.tasks.show', [$project, $task])
                ->with('success', 'Task created successfully.');
        }
        
        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    public function show(Request $request, Task $task, ?Project $project = null)
    {
        // If project is provided (from project-scoped route), verify task belongs to it
        if ($project) {
            // if ($task->project_id != $project->id) {
            //     abort(404, 'Task not found in this project.');
            // }
        }
        
        // Ensure task belongs to current workspace
        $workspaceId = session('current_workspace_id');
      
        if ($task->workspace_id != $workspaceId) {
            // abort(403, 'You do not have access to this task.');
        }
        
        // $this->authorize('view', $task);

        $task->load(['assignees', 'watchers', 'status', 'tags', 'comments.user', 'attachments', 'subtasks.status', 'project']);

        $statuses = $task->project->customStatuses;
        $users = $this->getAssignableUsersForCreation($workspaceId);

        return view('tasks.show', compact('task', 'statuses', 'users'));
    }

    public function edit(Request $request, Task $task, ?Project $project = null)
    {
        // If project is provided (from project-scoped route), verify task belongs to it
        if ($project) {
            if ($task->project_id !== $project->id) {
                abort(404, 'Task not found in this project.');
            }
        }
        
        // Ensure task belongs to current workspace
        $workspaceId = session('current_workspace_id');
        if ($task->workspace_id != $workspaceId) {
            abort(403, 'You do not have access to this task.');
        }
        
        $this->authorize('update', $task);

        $statuses = $task->project->customStatuses;
        $users = $this->getAssignableUsersForCreation($workspaceId);
        $tags = \App\Models\Tag::where('workspace_id', $task->workspace_id)->get();

        $sprints = \App\Models\Sprint::where('workspace_id', $workspaceId)
            ->whereIn('status', ['planning', 'active'])
            ->orderBy('start_date', 'desc')
            ->get();
        return view('tasks.edit', compact('task', 'statuses', 'users', 'tags', 'sprints'));
    }

    public function update(Request $request, Task $task, ?Project $project = null)
    {
        // If project is provided (from project-scoped route), verify task belongs to it
        if ($project) {
            if ($task->project_id !== $project->id) {
                abort(404, 'Task not found in this project.');
            }
        }
        
        // Ensure task belongs to current workspace
        $workspaceId = session('current_workspace_id');
        if ($task->workspace_id != $workspaceId) {
            abort(403, 'You do not have access to this task.');
        }
        
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'nullable|exists:custom_statuses,id',
            'priority' => 'nullable|in:urgent,high,normal,low,none',
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'estimated_time' => 'nullable|integer',
            'sprint_id' => 'nullable|exists:sprints,id',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        // Validate that assignees are valid for the current user
        if (!empty($validated['assignee_ids'])) {
            $allowedAssignees = $this->getAssignableUsersForCreation($workspaceId)->pluck('id')->toArray();
            foreach ($validated['assignee_ids'] as $assigneeId) {
                if (!in_array($assigneeId, $allowedAssignees)) {
                    return back()->with('error', 'You can only assign tasks to users you have permission to manage.');
                }
            }
        }

        if (isset($validated['estimated_time'])) {
            $validated['estimated_time'] = $validated['estimated_time'] * 60;
        }

        $this->taskService->update($task, $validated, auth()->user());

        // Use project-specific route if project exists, otherwise use standalone route
        $project = $task->project;
        if ($project) {
            return redirect()->route('projects.tasks.show', [$project, $task])
                ->with('success', 'Task updated successfully.');
        }
        
        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task, ?Project $project = null)
    {
        
        // If project is provided (from project-scoped route), verify task belongs to it
        // if ($project) {
        //     if ($task->project_id !== $project->id) {
        //         abort(404, 'Task not found in this project.');
        //     }
        // }
        
        // Ensure task belongs to current workspace
        $workspaceId = session('current_workspace_id');
        // if ($task->workspace_id !== $workspaceId) {
        //     abort(403, 'You do not have access to this task.');
        // }
        
        // $this->authorize('delete', $task);

        $project = $task->project;
        $this->taskService->delete($task, auth()->user());

        return redirect()->route('projects.show', $project)
            ->with('success', 'Task deleted successfully.');
    }

    public function updateStatus(Request $request, Task $task, ?Project $project = null)
    {
        // If project is provided (from project-scoped route), verify task belongs to it
        if ($project) {
            if ($task->project_id != $project->id) {
                abort(404, 'Task not found in this project.');
            }
        }
        
        // Ensure task belongs to current workspace
        $workspaceId = session('current_workspace_id');
        if ($task->workspace_id != $workspaceId) {
            abort(403, 'You do not have access to this task.');
        }
        
        $this->authorize('update', $task);

        $validated = $request->validate([
            'status_id' => 'required|exists:custom_statuses,id',
            'position' => 'nullable|integer',
        ]);

        $this->taskService->moveToStatus($task, $validated['status_id'], $validated['position'] ?? null, auth()->user());

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'status_id' => 'required|exists:custom_statuses,id',
            'project_id' => 'required|exists:projects,id',
        ]);

        $project = Project::find($validated['project_id']);
        $this->authorize('view', $project);

        $this->taskService->reorder($validated['task_ids'], $validated['status_id'], $project);

        return response()->json(['success' => true]);
    }
}
