<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
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
        
        // Only members and admins can create projects
        if (!auth()->user()->canCreateInWorkspace($workspaceId)) {
            abort(403, 'Guests cannot create projects.');
        }
        
        $workspace = Workspace::find($workspaceId);
        $spaces = $workspace?->spaces ?? collect();

        return view('projects.create', compact('spaces'));
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
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['workspace_id'] = $workspaceId;
        $validated['created_by_user_id'] = auth()->id();
        $validated['progress'] = 0;
        $validated['is_archived'] = false;

        $project = Project::create($validated);

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

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
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
