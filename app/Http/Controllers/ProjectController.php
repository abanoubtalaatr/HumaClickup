<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Workspace;
use App\Models\User;
use App\Models\Track;
use App\Services\ProjectPlanningService;
use Illuminate\Http\Request;

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

        // Guests can only see projects they have assigned tasks in
        if ($user->isGuestInWorkspace($workspaceId) && !$tester) {
            $projects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->whereHas('tasks', function ($query) use ($user) {
                    $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
                })
                ->withCount(['tasks' => function ($query) use ($user) {
                    $query->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
                }])
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
                ->withCount('tasks')
                ->with(['space', 'createdBy'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }
        // Regular members can only see projects they created
        else {
            $projects = Project::where('workspace_id', $workspaceId)
                ->where('is_archived', false)
                ->where('created_by_user_id', $user->id)
                ->withCount('tasks')
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

        return view('projects.create', compact('spaces', 'guests', 'groups', 'tracks'));
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
        
        $workspaceId = session('current_workspace_id');
        $workspace = Workspace::find($workspaceId);
        $spaces = $workspace?->spaces ?? collect();

        return view('projects.edit', compact('project', 'spaces'));
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
