<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, ?int $workspaceId = null): bool
    {
        $workspaceId = $workspaceId ?? session('current_workspace_id');
        
        if (!$workspaceId || !$user->belongsToWorkspace($workspaceId)) {
            return false;
        }

        // Guests can view tasks (but will be filtered to assigned only)
        if ($user->isGuestInWorkspace($workspaceId)) {
            return true;
        }

        return $user->hasPermissionInWorkspace('view_tasks', $workspaceId);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Check workspace membership
        if (!$user->belongsToWorkspace($task->workspace_id)) {
            return false;
        }

        // Guests can only view tasks assigned to them
        if ($user->isGuestInWorkspace($task->workspace_id)) {
            return $task->assignees->contains('id', $user->id);
        }

        // Private tasks: only creator, assignees, watchers, and admins can view
        if ($task->is_private) {
            return $user->id === $task->creator_id
                || $task->assignees->contains('id', $user->id)
                || $task->watchers->contains('id', $user->id)
                || in_array($user->getRoleInWorkspace($task->workspace_id), ['owner', 'admin']);
        }

        return $user->hasPermissionInWorkspace('view_tasks', $task->workspace_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, ?int $workspaceId = null, ?int $projectId = null): bool
    {
        $workspaceId = $workspaceId ?? session('current_workspace_id');
        
        if (!$workspaceId || !$user->belongsToWorkspace($workspaceId)) {
            return false;
        }

        // Guests cannot create tasks
        if ($user->isGuestInWorkspace($workspaceId)) {
            return false;
        }

        // Check if user has permission to create tasks
        if (!$user->hasPermissionInWorkspace('create_tasks', $workspaceId)) {
            return false;
        }

        // If project specified, check project access
        if ($projectId) {
            $project = \App\Models\Project::find($projectId);
            if (!$project || $project->workspace_id !== $workspaceId) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Check workspace membership
        if (!$user->belongsToWorkspace($task->workspace_id)) {
            return false;
        }

        // Guests can only update status on their assigned tasks (limited update)
        if ($user->isGuestInWorkspace($task->workspace_id)) {
            return $task->assignees->contains('id', $user->id);
        }

        // Private tasks: only creator, assignees, and admins can edit
        if ($task->is_private) {
            return $user->id === $task->creator_id
                || $task->assignees->contains('id', $user->id)
                || in_array($user->getRoleInWorkspace($task->workspace_id), ['owner', 'admin']);
        }

        return $user->hasPermissionInWorkspace('edit_tasks', $task->workspace_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Check workspace membership
        if (!$user->belongsToWorkspace($task->workspace_id)) {
            return false;
        }

        // Guests cannot delete tasks
        if ($user->isGuestInWorkspace($task->workspace_id)) {
            return false;
        }

        // Only creator, admins, and owners can delete
        $role = $user->getRoleInWorkspace($task->workspace_id);
        if (in_array($role, ['owner', 'admin'])) {
            return true;
        }

        if ($user->id === $task->creator_id) {
            return $user->hasPermissionInWorkspace('delete_tasks', $task->workspace_id);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        $role = $user->getRoleInWorkspace($task->workspace_id);
        return $role === 'owner';
    }

    /**
     * Determine whether the user can assign the task.
     */
    public function assign(User $user, Task $task): bool
    {
        // Guests cannot assign tasks
        if ($user->isGuestInWorkspace($task->workspace_id)) {
            return false;
        }
        
        return $this->update($user, $task);
    }

    /**
     * Determine whether the user can track time on the task.
     */
    public function trackTime(User $user, Task $task): bool
    {
        // Check workspace membership
        if (!$user->belongsToWorkspace($task->workspace_id)) {
            return false;
        }

        // Guests CAN track time on their assigned tasks
        if ($user->isGuestInWorkspace($task->workspace_id)) {
            return $task->assignees->contains('id', $user->id);
        }

        return $user->hasPermissionInWorkspace('track_time', $task->workspace_id);
    }
}
