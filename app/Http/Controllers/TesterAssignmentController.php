<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Workspace;
use App\Services\TesterAssignmentService;
use Illuminate\Http\Request;

class TesterAssignmentController extends Controller
{
    protected TesterAssignmentService $testerService;

    public function __construct(TesterAssignmentService $testerService)
    {
        $this->testerService = $testerService;
    }

    /**
     * Show assign testers form.
     */
    public function create(Request $request, Project $project)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check authorization (only testing leads or admins/owners)
        if (!$user->isAdminInWorkspace($workspaceId) && 
            !$user->isOwnerInWorkspace($workspaceId) &&
            !$user->hasTestingTrackInWorkspace($workspaceId)) {
            abort(403, 'Only testing leads can assign testers.');
        }

        // Get workspace
        $workspace = Workspace::find($workspaceId);

        // Get available testers
        $availableTesters = $this->testerService->getAvailableTesters($workspace);

        // Get recommended testers (balanced workload)
        $recommendedTesters = $this->testerService->getRecommendedTesters($workspace, 2);

        // Get already assigned testers
        $assignedTesters = $project->projectTesters()->with('user')->get();

        return view('projects.assign-testers', compact(
            'project',
            'availableTesters',
            'recommendedTesters',
            'assignedTesters'
        ));
    }

    /**
     * Store tester assignments.
     */
    public function store(Request $request, Project $project)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check authorization
        if (!$user->isAdminInWorkspace($workspaceId) && 
            !$user->isOwnerInWorkspace($workspaceId) &&
            !$user->hasTestingTrackInWorkspace($workspaceId)) {
            abort(403, 'Only testing leads can assign testers.');
        }

        $validated = $request->validate([
            'tester_ids' => 'required|array|min:1',
            'tester_ids.*' => 'required|exists:users,id',
        ]);

        // Assign testers
        $result = $this->testerService->assignTesters(
            $project,
            $validated['tester_ids'],
            $user
        );

        if (!empty($result['errors'])) {
            return back()
                ->with('warning', implode(' ', $result['errors']))
                ->with('success', count($result['assigned']) . ' tester(s) assigned successfully.');
        }

        return redirect()->route('projects.show', $project)
            ->with('success', count($result['assigned']) . ' tester(s) assigned successfully!');
    }
}
