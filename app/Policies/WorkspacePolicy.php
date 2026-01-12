<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can always see their own workspaces
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Workspace $workspace): bool
    {
        return $user->belongsToWorkspace($workspace->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a workspace
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Workspace $workspace): bool
    {
        if (!$user->belongsToWorkspace($workspace->id)) {
            return false;
        }

        return $user->hasPermissionInWorkspace('manage_workspace_settings', $workspace->id)
            || $user->id === $workspace->owner_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        // Only owner can delete workspace
        return $user->id === $workspace->owner_id;
    }

    /**
     * Determine whether the user can invite users.
     */
    public function inviteUsers(User $user, Workspace $workspace): bool
    {
        if (!$user->belongsToWorkspace($workspace->id)) {
            return false;
        }

        return $user->hasPermissionInWorkspace('invite_users', $workspace->id)
            || in_array($user->getRoleInWorkspace($workspace->id), ['owner', 'admin']);
    }

    /**
     * Determine whether the user can manage roles.
     */
    public function manageRoles(User $user, Workspace $workspace): bool
    {
        if (!$user->belongsToWorkspace($workspace->id)) {
            return false;
        }

        return $user->hasPermissionInWorkspace('manage_roles', $workspace->id)
            || in_array($user->getRoleInWorkspace($workspace->id), ['owner', 'admin']);
    }
}
