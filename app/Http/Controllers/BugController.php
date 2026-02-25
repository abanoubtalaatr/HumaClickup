<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Http\Requests\StoreBugRequest;
use App\Services\BugTrackingService;
use Illuminate\Http\Request;

class BugController extends Controller
{
    protected BugTrackingService $bugTrackingService;

    public function __construct(BugTrackingService $bugTrackingService)
    {
        $this->bugTrackingService = $bugTrackingService;
    }

    /**
     * Show the form for creating a new bug.
     */
    public function create(Request $request)
    {
        $mainTaskId = $request->query('main_task_id');
        $mainTask = null;
        
        if ($mainTaskId) {
            $mainTask = Task::findOrFail($mainTaskId);
            $this->authorize('view', $mainTask);
            
            // Check if user can create bugs (must be a tester)
            if (!auth()->user()->isTesterInProject($mainTask->project_id)) {
                abort(403, 'Only testers can create bugs.');
            }
        }
        
        return view('bugs.create', compact('mainTask'));
    }

    /**
     * Store a newly created bug in storage.
     */
    public function store(StoreBugRequest $request)
    {
        $mainTask = Task::findOrFail($request->main_task_id);
        $this->authorize('view', $mainTask);
        
        // Check if user can create bugs
        if (!auth()->user()->isTesterInProject($mainTask->project_id)) {
            abort(403, 'Only testers can create bugs.');
        }
        
        $result = $this->bugTrackingService->createBug(
            $mainTask,
            $request->validated(),
            auth()->user()
        );
        
        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error']])->withInput();
        }
        
        return redirect()
            ->route('tasks.show', $mainTask->id)
            ->with('success', 'Bug created successfully!');
    }

    /**
     * Display bugs for a main task.
     */
    public function index(Request $request)
    {
        $mainTaskId = $request->query('main_task_id');
        
        if ($mainTaskId) {
            $mainTask = Task::with(['bugs.status', 'bugs.assignees'])->findOrFail($mainTaskId);
            $this->authorize('view', $mainTask);
            
            $summary = $this->bugTrackingService->getBugTrackingSummary($mainTask);
            
            return view('bugs.index', compact('mainTask', 'summary'));
        }
        
        // Show all bugs in current workspace
        $workspaceId = session('current_workspace_id');
        $bugs = Task::where('workspace_id', $workspaceId)
            ->where('type', 'bug')
            ->with(['status', 'assignees', 'relatedTask', 'project'])
            ->latest()
            ->paginate(20);
        
        return view('bugs.list', compact('bugs'));
    }

    /**
     * Show bug details.
     */
    public function show(Task $bug)
    {
        if ($bug->type !== 'bug') {
            abort(404, 'Task is not a bug.');
        }
        
        $this->authorize('view', $bug);
        
        $bug->load(['status', 'assignees', 'relatedTask', 'comments.user', 'attachments']);
        
        return view('bugs.show', compact('bug'));
    }

    /**
     * Get bug tracking summary for a project.
     */
    public function projectSummary(Project $project)
    {
        $this->authorize('view', $project);
        
        $bugs = $this->bugTrackingService->getProjectBugs($project);
        
        $summary = [
            'total_bugs' => $bugs->count(),
            'open_bugs' => $bugs->filter(fn($bug) => $bug->status->type !== 'done')->count(),
            'closed_bugs' => $bugs->filter(fn($bug) => $bug->status->type === 'done')->count(),
            'bugs_by_priority' => $bugs->groupBy('priority')->map->count(),
        ];
        
        return view('bugs.project-summary', compact('project', 'bugs', 'summary'));
    }
}
