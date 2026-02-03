<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {}

    /**
     * Create a new task
     */
    public function create(array $data, User $user, Project $project): Task
    {
        return DB::transaction(function () use ($data, $user, $project) {
            // Get default status if not provided
            if (!isset($data['status_id'])) {
                $defaultStatus = $project->getDefaultStatus();
                if (!$defaultStatus) {
                    throw new \Exception('Project has no statuses defined.');
                }
                $data['status_id'] = $defaultStatus->id;
            }

            // Set required fields
            $data['workspace_id'] = $project->workspace_id;
            $data['project_id'] = $project->id;
            $data['creator_id'] = $user->id;
            $data['position'] = $data['position'] ?? $this->getNextPosition($project, $data['status_id']);

            $task = Task::create($data);

            // Assign default assignee if set
            if ($project->default_assignee_id && !isset($data['assignee_ids'])) {
                $task->assignees()->attach($project->default_assignee_id);
            }

            // Assign users if provided
            if (isset($data['assignee_ids']) && is_array($data['assignee_ids'])) {
                $task->assignees()->sync($data['assignee_ids']);
            }

            // Add tags if provided
            if (isset($data['tag_ids']) && is_array($data['tag_ids'])) {
                $task->tags()->sync($data['tag_ids']);
            }

            // Log activity
            $this->activityLogService->log(
                $project->workspace_id,
                $user->id,
                'created',
                $task,
                null,
                $task->toArray()
            );

            // Notify assignees (future: notification service)
            // NotificationService::notifyTaskAssigned($task);

            return $task->fresh();
        });
    }

    /**
     * Update a task
     */
    public function update(Task $task, array $data, User $user): Task
    {
        return DB::transaction(function () use ($task, $data, $user) {
            $oldValues = $task->toArray();

            // Handle status change
            if (isset($data['status_id']) && $data['status_id'] !== $task->status_id) {
                $this->handleStatusChange($task, $data['status_id'], $user);
            }

            // Update task
            $task->update($data);

            // Handle assignees
            if (isset($data['assignee_ids'])) {
                $oldAssignees = $task->assignees->pluck('id')->toArray();
                $task->assignees()->sync($data['assignee_ids']);
                
                // Notify newly assigned users
                $newAssignees = array_diff($data['assignee_ids'], $oldAssignees);
                // NotificationService::notifyTaskAssigned($task, $newAssignees);
            }

            // Handle tags
            if (isset($data['tag_ids'])) {
                $task->tags()->sync($data['tag_ids']);
            }

            // Log activity
            $this->activityLogService->log(
                $task->workspace_id,
                $user->id,
                'updated',
                $task,
                $oldValues,
                $task->fresh()->toArray()
            );

            // Update project progress if status changed (only when task belongs to a project)
            if (isset($data['status_id']) && $task->project) {
                $task->project->calculateProgress();
            }

            return $task->fresh();
        });
    }

    /**
     * Delete a task (soft delete)
     */
    public function delete(Task $task, User $user): bool
    {
        return DB::transaction(function () use ($task, $user) {
            // Stop any running timers on this task
            $task->timeEntries()
                ->whereNull('end_time')
                ->update(['end_time' => now()]);

            // Log activity
            $this->activityLogService->log(
                $task->workspace_id,
                $user->id,
                'deleted',
                $task,
                $task->toArray(),
                null
            );

            $deleted = $task->delete();

            // Update project progress only when task belonged to a project
            if ($task->project) {
                $task->project->calculateProgress();
            }

            return $deleted;
        });
    }

    /**
     * Move task to new status (for Kanban drag-drop)
     */
    public function moveToStatus(Task $task, int $statusId, ?int $position = null, User $user): Task
    {
        return DB::transaction(function () use ($task, $statusId, $position, $user) {
            $oldStatusId = $task->status_id;
            $positionValue = $position ?? ($task->project
                ? $this->getNextPosition($task->project, $statusId)
                : $this->getNextPositionForStatusWithoutProject($statusId));

            // Update by primary key only to avoid global scope affecting the update
            Task::withoutGlobalScopes()
                ->where('id', $task->id)
                ->update([
                    'status_id' => $statusId,
                    'position' => $positionValue,
                ]);

            $task->refresh();

            // Handle status change logic
            $this->handleStatusChange($task, $statusId, $user);

            // Log activity (use current workspace when task has no workspace_id)
            $workspaceIdForLog = $task->workspace_id ?? session('current_workspace_id');
            if ($workspaceIdForLog !== null) {
                $this->activityLogService->log(
                    $workspaceIdForLog,
                    $user->id,
                    'status_changed',
                    $task,
                    ['status_id' => $oldStatusId],
                    ['status_id' => $statusId]
                );
            }

            // Update project progress only when task belongs to a project
            if ($task->project) {
                $task->project->calculateProgress();
            }

            return $task->fresh();
        });
    }

    /**
     * Reorder tasks within a status
     */
    public function reorder(array $taskIds, int $statusId, Project $project): void
    {
        DB::transaction(function () use ($taskIds, $statusId, $project) {
            $position = 0;
            foreach ($taskIds as $taskId) {
                Task::where('id', $taskId)
                    ->where('project_id', $project->id)
                    ->where('status_id', $statusId)
                    ->update(['position' => $position]);
                $position += 100; // Use gaps to avoid frequent updates
            }
        });
    }

    /**
     * Handle status change logic
     */
    protected function handleStatusChange(Task $task, int $newStatusId, User $user): void
    {
        $newStatus = \App\Models\CustomStatus::find($newStatusId);

        // If moving to "done" type status
        if ($newStatus && $newStatus->type === 'done') {
            // Stop any running timers
            $task->timeEntries()
                ->whereNull('end_time')
                ->get()
                ->each(fn($entry) => $entry->stop());

            // Notify watchers
            // NotificationService::notifyTaskCompleted($task);

            // Check if this unblocks other tasks
            $this->checkUnblockedTasks($task);
        }
    }

    /**
     * Check if completing this task unblocks other tasks
     */
    protected function checkUnblockedTasks(Task $task): void
    {
        $blockedTasks = $task->blockingTasks()
            ->whereHas('status', fn($q) => $q->where('type', '!=', 'done'))
            ->get();

        foreach ($blockedTasks as $blockedTask) {
            // Check if all blocking tasks are done
            $allBlockersDone = $blockedTask->blockedByTasks()
                ->whereHas('status', fn($q) => $q->where('type', 'done'))
                ->count() === $blockedTask->blockedByTasks()->count();

            if ($allBlockersDone) {
                // Notify that task is no longer blocked
                // NotificationService::notifyTaskUnblocked($blockedTask);
            }
        }
    }

    /**
     * Get next position for a task in a status
     */
    protected function getNextPosition(Project $project, int $statusId): int
    {
        $maxPosition = Task::where('project_id', $project->id)
            ->where('status_id', $statusId)
            ->max('position') ?? 0;

        return $maxPosition + 100;
    }

    /**
     * Get next position for a task in a status when task has no project
     */
    protected function getNextPositionForStatusWithoutProject(int $statusId): int
    {
        $maxPosition = Task::withoutGlobalScopes()
            ->whereNull('project_id')
            ->where('status_id', $statusId)
            ->max('position') ?? 0;

        return $maxPosition + 100;
    }

    /**
     * Add dependency between tasks
     */
    public function addDependency(Task $task, Task $dependsOnTask, string $type = 'blocks'): void
    {
        // Prevent circular dependencies
        if ($this->wouldCreateCircularDependency($task, $dependsOnTask)) {
            throw new \Exception('This would create a circular dependency.');
        }

        $task->blockedByTasks()->attach($dependsOnTask->id, ['type' => $type]);
    }

    /**
     * Check if adding dependency would create circular dependency
     */
    protected function wouldCreateCircularDependency(Task $task, Task $dependsOnTask): bool
    {
        // If the task we depend on already depends on us (directly or indirectly)
        return $dependsOnTask->blockedByTasks()
            ->where('tasks.id', $task->id)
            ->exists();
    }
}

