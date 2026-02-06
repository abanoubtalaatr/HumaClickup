<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Notifications\BugCreatedNotification;

class BugTrackingService
{
    /**
     * Create a bug for a main task.
     */
    public function createBug(Task $mainTask, array $bugData, User $creator): array
    {
        // Validate main task
        if (!$mainTask->isMainTask()) {
            return [
                'success' => false,
                'error' => 'Bugs can only be created for main tasks.',
            ];
        }

        // Validate bug time allocation
        $bugEstimatedTime = $bugData['estimated_time'] ?? 0;
        
        if (!$this->canAddBugTime($mainTask, $bugEstimatedTime)) {
            return [
                'success' => false,
                'error' => $this->getBugTimeLimitMessage($mainTask, $bugEstimatedTime),
            ];
        }

        // Create bug task
        $bug = Task::create([
            'workspace_id' => $mainTask->workspace_id,
            'project_id' => $mainTask->project_id,
            'related_task_id' => $mainTask->id,
            'creator_id' => $creator->id,
            'title' => $bugData['title'],
            'description' => $bugData['description'] ?? null,
            'type' => 'bug',
            'priority' => $bugData['priority'] ?? 'medium',
            'estimated_time' => $bugEstimatedTime,
            'status_id' => $mainTask->status_id,
            'assigned_date' => now(),
        ]);

        // Update main task bug tracking
        $mainTask->addBugTime($bugEstimatedTime);

        // Assign bug to main task assignees
        if ($mainTask->assignees->isNotEmpty()) {
            $bug->assignees()->attach($mainTask->assignees->pluck('id'));
            
            // Notify assignees
            foreach ($mainTask->assignees as $assignee) {
                $assignee->notify(new BugCreatedNotification($bug, $mainTask, $creator));
            }
        }

        return [
            'success' => true,
            'bug' => $bug,
        ];
    }

    /**
     * Check if bug time can be added to main task.
     */
    public function canAddBugTime(Task $mainTask, float $bugHours): bool
    {
        // Initialize bug time limit if not set
        if (!$mainTask->bug_time_limit) {
            $mainTask->bug_time_limit = $mainTask->calculateBugTimeLimit();
            $mainTask->save();
        }

        return $mainTask->canAddBug($bugHours);
    }

    /**
     * Get bug time limit error message.
     */
    public function getBugTimeLimitMessage(Task $mainTask, float $requestedHours): string
    {
        $remaining = $mainTask->getRemainingBugTime();
        $limit = $mainTask->bug_time_limit;
        $used = $mainTask->bug_time_used;

        return "Bug time limit exceeded. Main task allows {$limit}h for bugs (20% of {$mainTask->estimated_time}h). " .
               "Currently used: {$used}h. Remaining: {$remaining}h. Requested: {$requestedHours}h.";
    }

    /**
     * Distribute bug time equally among multiple bugs.
     */
    public function distributeBugTime(Task $mainTask, int $bugsCount): float
    {
        if (!$mainTask->bug_time_limit) {
            $mainTask->bug_time_limit = $mainTask->calculateBugTimeLimit();
            $mainTask->save();
        }

        return $mainTask->bug_time_limit / $bugsCount;
    }

    /**
     * Get bugs for a main task.
     */
    public function getBugsForMainTask(Task $mainTask): \Illuminate\Database\Eloquent\Collection
    {
        return $mainTask->bugs;
    }

    /**
     * Get bug tracking summary for a main task.
     */
    public function getBugTrackingSummary(Task $mainTask): array
    {
        return [
            'main_task_estimated_time' => $mainTask->estimated_time,
            'bug_time_limit' => $mainTask->bug_time_limit,
            'bug_time_used' => $mainTask->bug_time_used,
            'bug_time_remaining' => $mainTask->getRemainingBugTime(),
            'bugs_count' => $mainTask->bugs_count,
            'bugs' => $mainTask->bugs->map(function ($bug) {
                return [
                    'id' => $bug->id,
                    'title' => $bug->title,
                    'estimated_time' => $bug->estimated_time,
                    'status' => $bug->status->name ?? null,
                ];
            }),
            'is_limit_exceeded' => $mainTask->isBugTimeLimitExceeded(),
        ];
    }

    /**
     * Validate bug creation data.
     */
    public function validateBugData(array $data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors[] = 'Bug title is required.';
        }

        if (empty($data['estimated_time']) || $data['estimated_time'] <= 0) {
            $errors[] = 'Bug estimated time must be greater than 0.';
        }

        if (empty($data['main_task_id'])) {
            $errors[] = 'Main task ID is required.';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Update bug time when bug is updated.
     */
    public function updateBugTime(Task $bug, float $oldEstimatedTime, float $newEstimatedTime): array
    {
        if ($bug->type !== 'bug' || !$bug->related_task_id) {
            return [
                'success' => false,
                'error' => 'Task is not a bug or has no related main task.',
            ];
        }

        $mainTask = $bug->relatedTask;
        $timeDifference = $newEstimatedTime - $oldEstimatedTime;

        // Check if new time exceeds limit
        if ($timeDifference > 0 && !$this->canAddBugTime($mainTask, $timeDifference)) {
            return [
                'success' => false,
                'error' => $this->getBugTimeLimitMessage($mainTask, $timeDifference),
            ];
        }

        // Update main task bug time
        $mainTask->bug_time_used += $timeDifference;
        $mainTask->save();

        return [
            'success' => true,
            'main_task' => $mainTask,
        ];
    }

    /**
     * Get all bugs for a project.
     */
    public function getProjectBugs(Project $project): \Illuminate\Database\Eloquent\Collection
    {
        return $project->tasks()->where('type', 'bug')->with('relatedTask')->get();
    }
}
