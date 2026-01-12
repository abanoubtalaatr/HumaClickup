<?php

namespace App\Http\Controllers;

use App\Models\Sprint;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    /**
     * Display a listing of sprints
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $projectId = $request->get('project_id');

        $query = Sprint::where('workspace_id', $workspaceId)
            ->with(['project', 'tasks.status'])
            ->withCount('tasks');

        if ($projectId) {
            $query->where('project_id', $projectId);
            $project = Project::find($projectId);
        } else {
            $project = null;
        }

        // Filter by status
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $sprints = $query->orderBy('start_date', 'desc')->get();

        // Get all projects for filter
        $projects = Project::where('workspace_id', $workspaceId)
            ->where('is_archived', false)
            ->orderBy('name')
            ->get();

        return view('sprints.index', compact('sprints', 'projects', 'project', 'status'));
    }

    /**
     * Show the form for creating a new sprint
     */
    public function create(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $projectId = $request->get('project_id');

        // Get all projects
        $projects = Project::where('workspace_id', $workspaceId)
            ->where('is_archived', false)
            ->orderBy('name')
            ->get();

        return view('sprints.create', compact('projects', 'projectId'));
    }

    /**
     * Store a newly created sprint
     */
    public function store(Request $request)
    {
        $workspaceId = session('current_workspace_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'goal' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:planning,active,completed,cancelled',
        ]);

        $validated['workspace_id'] = $workspaceId;

        $sprint = Sprint::create($validated);

        return redirect()->route('sprints.show', ['workspace' => $workspaceId, 'sprint' => $sprint->id])
            ->with('success', 'Sprint created successfully.');
    }

    /**
     * Display the specified sprint
     */
    public function show(Request $request,  $sprint)
    {
        
        $workspaceId = session('current_workspace_id');

        // Ensure sprint belongs to current workspace
        if ($request->sprint->workspace_id != $workspaceId) {
            abort(404, 'Sprint not found.');
        }

        $request->sprint->load(['project', 'tasks.status', 'tasks.assignees', 'tasks.project']);

        // Group tasks by status type
        $todoTasks = $request->sprint->tasks->filter(fn($t) => $t->status?->type === 'todo');
        $inProgressTasks = $request->sprint->tasks->filter(fn($t) => $t->status?->type === 'in_progress');
        $doneTasks = $request->sprint->tasks->filter(fn($t) => $t->status?->type === 'done');

        // Calculate metrics
        $metrics = [
            'total_tasks' => $request->sprint->tasks->count(),
            'completed_tasks' => $doneTasks->count(),
            'in_progress_tasks' => $inProgressTasks->count(),
            'todo_tasks' => $todoTasks->count(),
            'completion_percentage' => $request->sprint->completion_progress,
            'time_progress' => $request->sprint->time_progress,
            'days_remaining' => $request->sprint->days_remaining,
            'duration' => $request->sprint->duration,
        ];
        $sprint = $request->sprint;

        return view('sprints.show', compact('sprint', 'todoTasks', 'inProgressTasks', 'doneTasks', 'metrics'));
    }

    /**
     * Show the form for editing the sprint
     */
    public function edit(Request $request,  $sprint)
    {
        $workspaceId = session('current_workspace_id');

        // Ensure sprint belongs to current workspace
        if ($request->sprint->workspace_id != $workspaceId) {
            abort(404, 'Sprint not found.');
        }

        $sprint = $request->sprint;
        // Get all projects
        $projects = Project::where('workspace_id', $workspaceId)
            ->where('is_archived', false)
            ->orderBy('name')
            ->get();
        $sprint = $request->sprint;
        

        return view('sprints.edit', compact('sprint', 'projects'));
    }

    /**
     * Update the specified sprint
     */
    public function update(Request $request,  $sprint)
    {
        $workspaceId = session('current_workspace_id');

        // Ensure sprint belongs to current workspace
        if ($request->sprint->workspace_id != $workspaceId) {
            abort(404, 'Sprint not found.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'goal' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:planning,active,completed,cancelled',
        ]);

        $request->sprint->update($validated);
        $sprint = $request->sprint;

        return redirect()->route('sprints.show', ['workspace' => $workspaceId, 'sprint' => $sprint->id])
            ->with('success', 'Sprint updated successfully.');
    }

    /**
     * Remove the specified sprint
     */
    public function destroy(Request $request,  $sprint)
    {
        $workspaceId = session('current_workspace_id');

        // Ensure sprint belongs to current workspace
        if ($request->sprint->workspace_id != $workspaceId) {
            abort(404, 'Sprint not found.');
        }

        $request->sprint->delete();

        return redirect()->route('sprints.index', ['workspace' => $workspaceId])
            ->with('success', 'Sprint deleted successfully.');
    }

    /**
     * Quick action: Start sprint
     */
    public function start(Request $request,  $sprint)
    {
        $workspaceId = session('current_workspace_id');

        if ($request->sprint->workspace_id != $workspaceId) {
            abort(404, 'Sprint not found.');
        }

        $request->sprint->update(['status' => 'active']);

        return back()->with('success', 'Sprint started successfully.');
    }

    /**
     * Quick action: Complete sprint
     */
    public function complete(Request $request,  $sprint)
    {
        
        $workspaceId = session('current_workspace_id');

        if ($request->sprint->workspace_id != $workspaceId) {
            abort(404, 'Sprint not found.');
        }

        $request->sprint->update(['status' => 'completed']);

        return back()->with('success', 'Sprint completed successfully.');
    }
}
