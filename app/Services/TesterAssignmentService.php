<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Models\ProjectTester;
use App\Models\Workspace;
use App\Notifications\TesterAssignmentRequestNotification;
use App\Notifications\TesterAssignedToProjectNotification;

class TesterAssignmentService
{
    /**
     * Find testing track team leads in workspace.
     */
    public function findTestingTeamLeads(Workspace $workspace): \Illuminate\Database\Eloquent\Collection
    {
        return $workspace->users()
            ->whereHas('workspaces', function ($query) use ($workspace) {
                $query->where('workspace_id', $workspace->id)
                    ->where('role', 'member')
                    ->whereHas('track', function ($q) {
                        $q->where('name', 'Testing')
                          ->orWhere('slug', 'testing');
                    });
            })
            ->get();
    }

    /**
     * Request tester assignment when project is created.
     */
    public function requestTesterAssignment(Project $project, int $requiredTestersCount = 2): void
    {
        // Find testing track team leads
        $testingLeads = $this->findTestingTeamLeads($project->workspace);

        if ($testingLeads->isEmpty()) {
            // Log warning: No testing team leads found
            \Log::warning("No testing team leads found for workspace {$project->workspace_id}");
            return;
        }

        // Send notification to testing team leads
        foreach ($testingLeads as $lead) {
            $lead->notify(new TesterAssignmentRequestNotification($project, $requiredTestersCount));
        }
    }

    /**
     * Assign testers to project.
     */
    public function assignTesters(Project $project, array $testerIds, User $assignedBy): array
    {
        $assigned = [];
        $errors = [];

        foreach ($testerIds as $testerId) {
            try {
                $tester = User::findOrFail($testerId);

                // Validate tester is from testing track
                if (!$this->isValidTester($tester, $project->workspace_id)) {
                    $errors[] = "User {$tester->name} is not a valid tester.";
                    continue;
                }

                // Check if already assigned
                if ($this->isTesterAssigned($project, $tester)) {
                    $errors[] = "User {$tester->name} is already assigned as tester.";
                    continue;
                }

                // Assign tester
                $projectTester = ProjectTester::create([
                    'project_id' => $project->id,
                    'tester_id' => $tester->id,
                    'assigned_by_user_id' => $assignedBy->id,
                    'assigned_at' => now(),
                    'status' => 'active',
                ]);

                // Send notification to tester
                $tester->notify(new TesterAssignedToProjectNotification($project, $assignedBy));

                $assigned[] = $tester;

            } catch (\Exception $e) {
                $errors[] = "Error assigning tester ID {$testerId}: " . $e->getMessage();
            }
        }

        return [
            'assigned' => $assigned,
            'errors' => $errors,
        ];
    }

    /**
     * Check if user is a valid tester.
     */
    public function isValidTester(User $user, int $workspaceId): bool
    {
        return $user->hasTestingTrackInWorkspace($workspaceId);
    }

    /**
     * Check if tester is already assigned to project.
     */
    public function isTesterAssigned(Project $project, User $tester): bool
    {
        return ProjectTester::where('project_id', $project->id)
            ->where('tester_id', $tester->id)
            ->exists();
    }

    /**
     * Remove tester from project.
     */
    public function removeTester(Project $project, User $tester): bool
    {
        return ProjectTester::where('project_id', $project->id)
            ->where('tester_id', $tester->id)
            ->delete() > 0;
    }

    /**
     * Get available testers in workspace.
     */
    public function getAvailableTesters(Workspace $workspace): \Illuminate\Database\Eloquent\Collection
    {
        return $workspace->users()
            ->whereHas('workspaces', function ($query) use ($workspace) {
                $query->where('workspace_id', $workspace->id)
                    ->whereHas('track', function ($q) {
                        $q->where('name', 'Testing')
                          ->orWhere('slug', 'testing');
                    });
            })
            ->get();
    }

    /**
     * Calculate required testers count based on project teams.
     */
    public function calculateRequiredTestersCount(int $developmentTeamsCount): int
    {
        // Each team needs 2 testers as per requirements
        return $developmentTeamsCount * 2;
    }

    /**
     * Get tester workload (number of projects assigned).
     */
    public function getTesterWorkload(User $tester): int
    {
        return ProjectTester::where('tester_id', $tester->id)
            ->where('status', 'active')
            ->count();
    }

    /**
     * Get recommended testers based on workload.
     */
    public function getRecommendedTesters(Workspace $workspace, int $count = 2): \Illuminate\Database\Eloquent\Collection
    {
        $availableTesters = $this->getAvailableTesters($workspace);

        // Sort by workload (ascending) to balance load
        return $availableTesters->sortBy(function ($tester) {
            return $this->getTesterWorkload($tester);
        })->take($count);
    }
}
