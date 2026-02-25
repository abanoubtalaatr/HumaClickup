<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\Attachment;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        // Apply type filter (task/bug)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Guests can only see tasks assigned to them
        if ($isGuest) {
            $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
        }

        $tasks = $query->with(['assignees', 'status', 'tags', 'project', 'relatedTask', 'creator'])
            ->withCount(['comments', 'attachments', 'subtasks', 'bugs'])
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

        // Get tasks for related task selector (only for bug creation)
        $relatedTasks = collect();
        if ($project) {
            $relatedTasks = Task::where('workspace_id', $workspaceId)
                ->where('project_id', $project->id)
                ->where('type', 'task')
                ->get();
        }

        return view('tasks.list', compact('tasks', 'project', 'projects', 'statuses', 'assignees', 'tags', 'sprints', 'isGuest', 'relatedTasks'));
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
        
        
        // Determine project - check route parameter first, then query parameter
        if (!$project) {
            $selectedProjectId = $request->get('project_id');
            if ($selectedProjectId) {
                $project = Project::where('workspace_id', $workspaceId)->find($selectedProjectId);
            }
        }
        
        // Get statuses from the project
        if ($project) {
            $statuses = $project->customStatuses()->orderBy('order')->get();
        } elseif ($allProjects->count() > 0) {
            // If no project selected, get statuses from first project
            $statuses = $allProjects->first()->customStatuses()->orderBy('order')->get();
        } else {
            $statuses = collect();
        }
        

        // Load tasks for each status
        foreach ($statuses as $status) {
            $taskQuery = Task::where('status_id', $status->id);
            // When no project selected, include tasks with null workspace_id (bypass scope so they appear and can be moved)
            if ($project || $request->filled('project_id')) {
                $taskQuery->where('workspace_id', $workspaceId);
            } else {
                $taskQuery->withoutGlobalScope('workspace')
                    ->where(function ($q) use ($workspaceId) {
                        $q->where('workspace_id', $workspaceId)->orWhereNull('workspace_id');
                    });
            }

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
            
            // Apply type filter (task/bug)
            if ($request->filled('type')) {
                $taskQuery->where('type', $request->type);
            }
            
            // Guests can only see tasks assigned to them
            if ($isGuest && !$tester) {
                $taskQuery->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
            }
            
            $status->tasks = $taskQuery
                ->with(['assignees', 'tags', 'relatedTask', 'creator'])
                ->withCount(['comments', 'attachments', 'subtasks', 'bugs'])
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

        // Get tasks for related task selector (only for bug creation)
        $tasks = collect();
        if ($project) {
            $tasks = Task::where('workspace_id', $workspaceId)
                ->where('project_id', $project->id)
                ->where('type', 'task')
                ->get();
        }

        return response()
            ->view('tasks.kanban', compact('statuses', 'project', 'projects', 'assignees', 'tags', 'users', 'sprints', 'isGuest', 'tester', 'tasks'))
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
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
        
        // Get statuses - if project is provided, get its statuses, otherwise get from first project or empty
        if ($project) {
            $statuses = $project->customStatuses()->orderBy('order')->get();
        } elseif ($projects->count() > 0) {
            // If no specific project but projects exist, get statuses from first project
            $statuses = $projects->first()->customStatuses()->orderBy('order')->get();
        } else {
            $statuses = collect();
        }

       
        $users = $this->getAssignableUsersForCreation($workspaceId);
        $tags = \App\Models\Tag::where('workspace_id', $workspaceId)->get();
        
        // Get active sprints for the workspace
        $sprints = \App\Models\Sprint::where('workspace_id', $workspaceId)
            ->whereIn('status', ['planning', 'active'])
            ->orderBy('start_date', 'desc')
            ->get();

        // Get tasks for related task selector (only for bug creation)
        $tasks = collect();
        if ($project) {
            $tasks = Task::where('workspace_id', $workspaceId)
                ->where('project_id', $project->id)
                ->where('type', 'task')
                ->get();
        }

        return view('tasks.create', compact('project', 'projects', 'statuses', 'users', 'tags', 'sprints', 'tasks'));
    }

    public function store(Request $request, ?Project $project = null)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:task,bug',
            'related_task_id' => 'nullable|exists:tasks,id',
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
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB per file
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

        // Ensure type is set (should be from validation, but set default as fallback)
        if (!isset($validated['type']) || empty($validated['type'])) {
            $validated['type'] = 'task';
        }

        $task = $this->taskService->create($validated, auth()->user(), $project);

        // Attach uploaded files to the task
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store(
                    sprintf('attachments/workspace_%s/task_%s', $workspaceId, $task->id),
                    'local'
                );
                Attachment::create([
                    'attachable_type' => Task::class,
                    'attachable_id' => $task->id,
                    'user_id' => $user->id,
                    'filename' => basename($path),
                    'original_filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'disk' => 'local',
                    'path' => $path,
                ]);
            }
        }

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

        $task->load([
            'assignees', 'watchers', 'status', 'tags', 'comments.user',
            'attachments', 'subtasks.status', 'subtasks.assignees', 'subtasks.comments.user',
            'project', 'creator',
            'bugs.status', 'bugs.assignees', 'bugs.creator', 'bugs.comments.user',
        ]);

        $statuses = $task->project->customStatuses;
        $users = $this->getAssignableUsersForCreation($workspaceId);

        // Check if current user is a tester for this project
        $user = auth()->user();
        $isTester = $task->project->testers()->where('tester_id', $user->id)->where('status', 'active')->exists();
        $isGuestTester = $user->hasTestingTrackInWorkspace($workspaceId);
        $canReportBugs = $isTester || $isGuestTester || $user->isAdminInWorkspace($workspaceId) || $user->isOwnerInWorkspace($workspaceId);

        return view('tasks.show', compact('task', 'statuses', 'users', 'canReportBugs'));
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
        
        // Get tasks for related task selector (only for bugs)
        $tasks = collect();
        if ($task->project) {
            $tasks = Task::where('workspace_id', $workspaceId)
                ->where('project_id', $task->project->id)
                ->where('type', 'task')
                ->where('id', '!=', $task->id) // Exclude current task
                ->get();
        }
        
        return view('tasks.edit', compact('task', 'statuses', 'users', 'tags', 'sprints', 'tasks'));
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
            'type' => 'nullable|in:task,bug',
            'related_task_id' => 'nullable|exists:tasks,id',
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
        // if ($project) {
        //     if ($task->project_id != $project->id) {
        //         abort(404, 'Task not found in this project.');
        //     }
        // }
        
        // Ensure task belongs to current workspace
        $workspaceId = session('current_workspace_id');
        // if ($task->workspace_id != $workspaceId) {
        //     abort(403, 'You do not have access to this task.');
        // }
        
        // $this->authorize('update', $task);

        $validated = $request->validate([
            'status_id' => 'required',
            'position' => 'nullable|integer',
        ]);

        $this->taskService->moveToStatus($task, (int) $validated['status_id'], $validated['position'] ?? null, auth()->user());

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

    public function bugs(Request $request, ?Project $project = null)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        $isGuest = $user->isGuestInWorkspace($workspaceId);
        $tester = $user->hasTestingTrackInWorkspace($workspaceId);
        
        // Get all projects for filter based on user role
        if ($isGuest && !$tester) {
            $allProjects = Project::where('workspace_id', $workspaceId)
                ->whereHas('tasks', function ($query) use ($user) {
                    $query->where('type', 'bug')
                        ->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
                })
                ->get();
        } elseif ($user->isAdminInWorkspace($workspaceId) 
            || $user->isOwnerInWorkspace($workspaceId) 
            || $user->hasTestingTrackInWorkspace($workspaceId)) {
            $allProjects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->get();
        } else {
            $allProjects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->where('created_by_user_id', $user->id)
                ->get();
        }
        
        // Determine which project to show and find a project that has bugs for statuses
        $selectedProjectId = $request->get('project_id');
        
        // First, find a project that has bugs (for getting statuses)
        $projectWithBugs = null;
        if ($selectedProjectId) {
            // Check if selected project has bugs
            $selectedProject = Project::where('workspace_id', $workspaceId)->find($selectedProjectId);
            if ($selectedProject && $selectedProject->tasks()->where('type', 'bug')->exists()) {
                $projectWithBugs = $selectedProject;
            }
        }
        
        // If no project with bugs found yet, search for any project with bugs
        if (!$projectWithBugs) {
            $projectWithBugs = Project::where('workspace_id', $workspaceId)
                ->whereHas('tasks', function ($query) {
                    $query->where('type', 'bug');
                })
                ->first();
        }
        
        // Determine which project to use for filtering
        if (!$project) {
            if ($selectedProjectId) {
                $project = Project::where('workspace_id', $workspaceId)->find($selectedProjectId);
            } else {
                // Use project with bugs if found, otherwise first project
                $project = $projectWithBugs ?? $allProjects->first();
            }
        }
        
        $query = Task::where('workspace_id', $workspaceId)
            ->where('type', 'bug');
        
        // Apply filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
            // Update project to the selected one
            if (!$project || $project->id != $request->project_id) {
                $project = Project::where('workspace_id', $workspaceId)->find($request->project_id);
            }
        } elseif ($project) {
            $query->where('project_id', $project->id);
        }
        
        // Get statuses from a project that has bugs (priority) or selected project
        // This ensures we show statuses from a project that actually has bugs
        if ($projectWithBugs) {
            // Always prefer statuses from a project that has bugs
            $statuses = $projectWithBugs->customStatuses()->orderBy('order')->get();
        } elseif ($project) {
            // Fallback to selected/current project's statuses
            $statuses = $project->customStatuses()->orderBy('order')->get();
        } else {
            // Last resort: get from any project
            $fallbackProject = $allProjects->first();
            $statuses = $fallbackProject ? $fallbackProject->customStatuses()->orderBy('order')->get() : collect();
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

        if ($request->filled('creator_id')) {
            $query->where('creator_id', $request->creator_id);
        }

        // Guests can only see bugs assigned to them
        if ($isGuest && !$tester) {
            $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
        }

        $bugs = $query->with(['assignees', 'status', 'tags', 'project', 'relatedTask', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $assignees = $this->getAssignableUsersForCreation($workspaceId);
        $tags = \App\Models\Tag::where('workspace_id', $workspaceId)->get();
        $projects = $allProjects;
        
        // Get all tasks for related task selector (only if project is selected)
        $tasks = collect();
        if ($project) {
            $tasks = Task::where('workspace_id', $workspaceId)
                ->where('project_id', $project->id)
                ->where('type', 'task')
                ->get();
        }

        return view('bugs.index', compact('bugs', 'project', 'projects', 'statuses', 'assignees', 'tags', 'tasks', 'isGuest'));
    }

    /**
     * Store a bug report on a main task.
     * Auto-calculates estimation: all bugs share 20% of main task time equally.
     */
    public function storeBug(Request $request, Task $task)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Verify user can report bugs (tester or admin/owner)
        $isTester = $task->project->testers()->where('tester_id', $user->id)->where('status', 'active')->exists();
        $isGuestTester = $user->hasTestingTrackInWorkspace($workspaceId);
        $canReportBugs = $isTester || $isGuestTester || $user->isAdminInWorkspace($workspaceId) || $user->isOwnerInWorkspace($workspaceId);

        if (!$canReportBugs) {
            return back()->with('error', 'You do not have permission to report bugs.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:urgent,high,normal,low',
        ]);

        $todoStatus = $task->project->customStatuses()->where('type', 'todo')->first();

        // Create the bug task
        $bug = Task::create([
            'workspace_id' => $workspaceId,
            'project_id' => $task->project_id,
            'creator_id' => $user->id,
            'related_task_id' => $task->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => 'bug',
            'priority' => $validated['priority'] ?? 'normal',
            'status_id' => $todoStatus?->id,
            'is_main_task' => 'no',
            'assigned_date' => now(),
        ]);

        // Assign the bug to the same assignee as the main task
        $mainAssignee = $task->assignees()->first();
        if ($mainAssignee) {
            $bug->assignees()->attach($mainAssignee->id);
        }

        // Recalculate estimation for ALL bugs on this main task
        $this->recalculateBugEstimations($task);

        return back()->with('success', 'Bug reported successfully.');
    }

    /**
     * Recalculate bug estimations so all bugs share 20% of main task time equally.
     */
    private function recalculateBugEstimations(Task $mainTask): void
    {
        $bugs = $mainTask->bugs()->get();
        $bugsCount = $bugs->count();

        if ($bugsCount === 0) {
            $mainTask->update([
                'bugs_count' => 0,
                'bug_time_used' => 0,
            ]);
            return;
        }

        // 20% of main task estimated time
        $percentage = $mainTask->project->bug_time_allocation_percentage ?? 20;
        $totalBugTime = (($mainTask->estimated_time ?? 0) * $percentage) / 100;

        // Divide equally among all bugs
        $perBugTime = $totalBugTime / $bugsCount;

        foreach ($bugs as $bug) {
            $bug->update([
                'estimated_time' => round($perBugTime, 2),
            ]);
        }

        // Update main task bug tracking
        $mainTask->update([
            'bugs_count' => $bugsCount,
            'bug_time_used' => $totalBugTime,
            'bug_time_limit' => $totalBugTime,
        ]);
    }
}
