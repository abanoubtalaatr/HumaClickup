<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
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

        // Guests can only view assigned projects
        if ($user->isGuestInWorkspace($workspaceId)) {
            return true; // Will be filtered in the controller
        }

        return $user->hasPermissionInWorkspace('view_projects', $workspaceId);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        if (!$user->belongsToWorkspace($project->workspace_id)) {
            return false;
        }

        // Guests can only view projects they have tasks assigned to
        if ($user->isGuestInWorkspace($project->workspace_id)) {
            return $project->tasks()
                ->whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
                ->exists();
        }

        return $user->hasPermissionInWorkspace('view_projects', $project->workspace_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, ?int $workspaceId = null): bool
    {
        $workspaceId = $workspaceId ?? session('current_workspace_id');
        
        if (!$workspaceId || !$user->belongsToWorkspace($workspaceId)) {
            return false;
        }

        // Guests cannot create projects
        if ($user->isGuestInWorkspace($workspaceId)) {
            return false;
        }

        return $user->hasPermissionInWorkspace('create_projects', $workspaceId);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        if (!$user->belongsToWorkspace($project->workspace_id)) {
            return false;
        }

        // Guests cannot update projects
        if ($user->isGuestInWorkspace($project->workspace_id)) {
            return false;
        }

        return $user->hasPermissionInWorkspace('edit_projects', $project->workspace_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        if (!$user->belongsToWorkspace($project->workspace_id)) {
            return false;
        }

        // Guests cannot delete projects
        if ($user->isGuestInWorkspace($project->workspace_id)) {
            return false;
        }

        return $user->hasPermissionInWorkspace('delete_projects', $project->workspace_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        $role = $user->getRoleInWorkspace($project->workspace_id);
        return $role === 'owner';
    }
}
