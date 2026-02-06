<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Workspace;
use App\Http\Requests\AssignTestersRequest;
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
     * Show form to assign testers to a project.
     */
    public function create(Project $project)
    {
        $this->authorize('update', $project);
        
        $workspace = Workspace::findOrFail(session('current_workspace_id'));
        $availableTesters = $this->testerService->getAvailableTesters($workspace);
        $currentTesters = $project->activeTesters;
        
        // Get recommended testers based on workload
        $recommendedTesters = $this->testerService->getRecommendedTesters($workspace, 2);
        
        // Calculate workload for each tester
        $testersWithWorkload = $availableTesters->map(function ($tester) use ($testerService) {
            return [
                'user' => $tester,
                'workload' => $this->testerService->getTesterWorkload($tester),
            ];
        });
        
        return view('testers.assign', compact(
            'project',
            'testersWithWorkload',
            'currentTesters',
            'recommendedTesters'
        ));
    }

    /**
     * Assign testers to project.
     */
    public function store(AssignTestersRequest $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $result = $this->testerService->assignTesters(
            $project,
            $request->tester_ids,
            auth()->user()
        );
        
        $message = count($result['assigned']) . ' tester(s) assigned successfully.';
        
        if (!empty($result['errors'])) {
            $message .= ' Some assignments failed: ' . implode(', ', $result['errors']);
        }
        
        return redirect()
            ->route('projects.show', $project->id)
            ->with('success', $message);
    }

    /**
     * Remove a tester from project.
     */
    public function destroy(Project $project, $testerId)
    {
        $this->authorize('update', $project);
        
        $tester = \App\Models\User::findOrFail($testerId);
        
        $removed = $this->testerService->removeTester($project, $tester);
        
        if ($removed) {
            return back()->with('success', 'Tester removed successfully.');
        }
        
        return back()->withErrors(['error' => 'Failed to remove tester.']);
    }

    /**
     * Show testers for a project.
     */
    public function index(Project $project)
    {
        $this->authorize('view', $project);
        
        $testers = $project->testers()
            ->with('tester')
            ->latest()
            ->get();
        
        return view('testers.index', compact('project', 'testers'));
    }
}
