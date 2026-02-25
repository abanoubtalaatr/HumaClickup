<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'timezone',
        'locale',
        'preferences',
        'last_activity_at',
        'status',
        'whatsapp_number',
        'slack_channel_link',
        'current_week_hours',
        'week_start_date',
        'meets_weekly_target',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'last_activity_at' => 'datetime',
            'current_week_hours' => 'decimal:2',
            'week_start_date' => 'date',
            'meets_weekly_target' => 'boolean',
        ];
    }

    // Relationships
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_user')
            ->withPivot('role', 'permissions', 'joined_at', 'track', 'track_id', 'created_by_user_id')
            ->withTimestamps();
    }

    public function ownedWorkspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'creator_id');
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignees')
            ->withTimestamps();
    }

    // Alias for assignedTasks for easier counting
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignees')
            ->withTimestamps();
    }

    public function watchedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_watchers')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Groups where this user is assigned (for guests).
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->withPivot('role', 'assigned_by_user_id', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Projects where this user is assigned as tester.
     */
    public function testerProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_testers', 'tester_id', 'project_id')
            ->withPivot('assigned_by_user_id', 'assigned_at', 'status', 'notes')
            ->withTimestamps();
    }

    /**
     * Daily progress records.
     */
    public function dailyProgress(): HasMany
    {
        return $this->hasMany(DailyProgress::class);
    }

    /**
     * Attendance records.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'guest_id');
    }

    /**
     * Attendances checked by this user as mentor.
     */
    public function mentorAttendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'mentor_id');
    }

    /**
     * Groups created by this user (for members).
     */
    public function createdGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'created_by_user_id');
    }

    // Reports (as member/mentor)
    public function submittedReports(): HasMany
    {
        return $this->hasMany(GuestReport::class, 'member_id');
    }

    // Reports (as guest receiving feedback)
    public function receivedReports(): HasMany
    {
        return $this->hasMany(GuestReport::class, 'guest_id');
    }

    // Helper Methods
    public function belongsToWorkspace(int $workspaceId): bool
    {
        return $this->workspaces()->where('workspaces.id', $workspaceId)->exists();
    }

    public function getRoleInWorkspace(int $workspaceId): ?string
    {
        $workspace = $this->workspaces()->where('workspaces.id', $workspaceId)->first();
        return $workspace?->pivot->role;
    }

    /**
     * Get the track name for this user in a workspace (uses current workspace from session if not passed).
     */
    public function getTrackNameInWorkspace(?int $workspaceId = null): ?string
    {
        $workspaceId = $workspaceId ?? session('current_workspace_id');
        if (!$workspaceId) {
            return null;
        }
        $track = $this->getTrackInWorkspace($workspaceId);
        return $track?->name;
    }
    /**
     * Get the pivot data for a specific workspace
     */
    public function getWorkspacePivot(int $workspaceId)
    {
        $workspace = $this->workspaces()->where('workspaces.id', $workspaceId)->first();
        return $workspace?->pivot;
    }

    /**
     * Get the track in a specific workspace
     */
    public function getTrackInWorkspace(int $workspaceId): ?Track
    {
        $workspace = $this->workspaces()->where('workspaces.id', $workspaceId)->first();
        $trackId = $workspace?->pivot->track_id;
        return $trackId ? Track::find($trackId) : null;
    }

    /**
     * Get the track ID in a specific workspace
     */
    public function getTrackIdInWorkspace(int $workspaceId): ?int
    {
        $workspace = $this->workspaces()->where('workspaces.id', $workspaceId)->first();
        return $workspace?->pivot->track_id;
    }

    /**
     * Get who created this user's membership in a workspace
     */
    public function getCreatedByInWorkspace(int $workspaceId): ?int
    {
        $workspace = $this->workspaces()->where('workspaces.id', $workspaceId)->first();
        return $workspace?->pivot->created_by_user_id;
    }

    public function hasPermissionInWorkspace(string $permission, int $workspaceId): bool
    {
        // Check workspace membership
        if (!$this->belongsToWorkspace($workspaceId)) {
            return false;
        }

        $role = $this->getRoleInWorkspace($workspaceId);
        
        // Owner has all permissions
        if ($role === 'owner') {
            return true;
        }

        // Permission matrix for each role
        $permissions = [
            'admin' => [
                'view_workspace', 'create_workspace', 'edit_workspace', 'delete_workspace',
                'view_projects', 'create_projects', 'edit_projects', 'delete_projects',
                'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks',
                'assign_tasks', 'track_time', 'view_time_tracking', 'view_all_time_tracking',
                'manage_members', 'manage_all_members', 'manage_tracks',
                'view_reports', 'view_all_reports',
            ],
            'member' => [
                'view_workspace',
                'view_projects', 'create_projects', 'edit_projects', 'delete_projects',
                'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks',
                'assign_tasks', 'track_time', 'view_time_tracking', 'view_all_time_tracking',
                'manage_guests', // Members can only manage guests they created
                'view_reports', 'view_all_reports',
            ],
            'guest' => [
                'view_workspace',
                'view_assigned_projects', 'view_assigned_tasks',
                'track_time', 'view_own_time_tracking',
                'view_own_reports',
            ],
        ];

        return in_array($permission, $permissions[$role] ?? []);
    }

    public function getActiveTimer(): ?TimeEntry
    {
        return $this->timeEntries()
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();
    }

    /**
     * Check if user is owner in the given workspace
     */
    public function isOwnerInWorkspace(int $workspaceId): bool
    {
        return $this->getRoleInWorkspace($workspaceId) === 'owner';
    }

    /**
     * Check if user is admin in the given workspace
     */
    public function isAdminInWorkspace(int $workspaceId): bool
    {
        return in_array($this->getRoleInWorkspace($workspaceId), ['owner', 'admin']);
    }

    /**
     * Check if user is member (not guest) in the given workspace
     */
    public function isMemberInWorkspace(int $workspaceId): bool
    {
        return in_array($this->getRoleInWorkspace($workspaceId), ['owner', 'admin', 'member']);
    }

    /**
     * Check if user has only member role (not admin or owner)
     */
    public function isMemberOnlyInWorkspace(int $workspaceId): bool
    {
        return $this->getRoleInWorkspace($workspaceId) === 'member';
    }

    /**
     * Check if user is guest in the given workspace
     */
    public function isGuestInWorkspace(int $workspaceId): bool
    {
        return $this->getRoleInWorkspace($workspaceId) === 'guest';
    }

    /**
     * Check if user can create projects/spaces in workspace
     */
    public function canCreateInWorkspace(int $workspaceId): bool
    {
        return $this->isMemberInWorkspace($workspaceId);
    }

    /**
     * Check if user can manage all members in workspace (admins only)
     */
    public function canManageAllMembersInWorkspace(int $workspaceId): bool
    {
        return $this->isAdminInWorkspace($workspaceId);
    }

    /**
     * Check if user can manage guests in workspace (members can manage guests)
     */
    public function canManageGuestsInWorkspace(int $workspaceId): bool
    {
        return $this->isMemberInWorkspace($workspaceId);
    }

    /**
     * Check if user can manage all members in workspace (backwards compatibility)
     * @deprecated Use canManageAllMembersInWorkspace instead
     */
    public function canManageMembersInWorkspace(int $workspaceId): bool
    {
        return $this->isMemberInWorkspace($workspaceId);
    }

    /**
     * Check if user can manage tracks in workspace (admin only)
     */
    public function canManageTracksInWorkspace(int $workspaceId): bool
    {
        return $this->isAdminInWorkspace($workspaceId);
    }

    /**
     * Check if user has testing track in workspace
     */
    public function hasTestingTrackInWorkspace(int $workspaceId): bool
    {
        $track = $this->getTrackInWorkspace($workspaceId);
        return $track && strtolower($track->name) === 'testing';
    }

    /** Track names (case-insensitive) that can use Pull Requests when no track_ids config */
    private const PULL_REQUEST_TRACK_NAMES = ['backend', 'frontend', 'mobile'];

    /**
     * Check if user's track in workspace is allowed for Pull Requests (by config track_ids or names: backend, frontend, mobile).
     */
    public function hasPullRequestTrackInWorkspace(int $workspaceId): bool
    {
        $trackId = $this->getTrackIdInWorkspace($workspaceId);
        $trackIds = config('pull_requests.track_ids', []);
        if (!empty($trackIds)) {
            return $trackId && in_array((int) $trackId, array_map('intval', $trackIds), true);
        }
        $track = $this->getTrackInWorkspace($workspaceId);
        if (!$track) {
            return false;
        }
        return in_array(strtolower(trim($track->name ?? '')), self::PULL_REQUEST_TRACK_NAMES, true);
    }

    /**
     * Check if member can see all projects/tasks (admin or testing track member)
     */
    public function canSeeAllProjectsInWorkspace(int $workspaceId): bool
    {
        return $this->isAdminInWorkspace($workspaceId)
            || $this->isOwnerInWorkspace($workspaceId)
            || ($this->isMemberOnlyInWorkspace($workspaceId) && $this->hasTestingTrackInWorkspace($workspaceId));
    }

    /**
     * Get guests created by this user in a workspace
     */
    public function getCreatedGuestsInWorkspace(int $workspaceId)
    {
        return User::whereHas('workspaces', function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId)
                  ->where('role', 'guest')
                  ->where('created_by_user_id', $this->id);
        })->get();
    }

    /**
     * Check if this user can manage a specific member
     */
    public function canManageMember(User $member, int $workspaceId): bool
    {
        // Admins can manage all (except owner)
        if ($this->isAdminInWorkspace($workspaceId)) {
            return !$member->isOwnerInWorkspace($workspaceId);
        }
        
        // Members can only manage guests they created
        if ($this->isMemberOnlyInWorkspace($workspaceId)) {
            $createdBy = $member->getCreatedByInWorkspace($workspaceId);
            return $member->isGuestInWorkspace($workspaceId) && $createdBy === $this->id;
        }
        
        return false;
    }

    /**
     * Update weekly hours tracking.
     */
    public function updateWeeklyHours(float $hours): void
    {
        // Check if we need to reset the week
        if (!$this->week_start_date || $this->week_start_date->lt(now()->startOfWeek())) {
            $this->resetWeeklyTracking();
        }

        $this->increment('current_week_hours', $hours);
        $this->checkWeeklyTarget();
    }

    /**
     * Reset weekly tracking.
     */
    public function resetWeeklyTracking(): void
    {
        $this->update([
            'current_week_hours' => 0,
            'week_start_date' => now()->startOfWeek(),
            'meets_weekly_target' => false,
        ]);
    }

    /**
     * Check if weekly target is met (30 hours).
     */
    public function checkWeeklyTarget(float $targetHours = 30): void
    {
        $meetsTarget = $this->current_week_hours >= $targetHours;
        
        if ($this->meets_weekly_target !== $meetsTarget) {
            $this->update(['meets_weekly_target' => $meetsTarget]);
        }
    }

    /**
     * Get progress towards weekly target.
     */
    public function getWeeklyProgress(float $targetHours = 30): array
    {
        return [
            'current_hours' => $this->current_week_hours,
            'target_hours' => $targetHours,
            'percentage' => min(100, ($this->current_week_hours / $targetHours) * 100),
            'remaining_hours' => max(0, $targetHours - $this->current_week_hours),
            'meets_target' => $this->meets_weekly_target,
        ];
    }

    /**
     * Get today's completed tasks for this user in a project.
     */
    public function getTodayCompletedTasks(int $projectId): int
    {
        return $this->assignedTasks()
            ->where('project_id', $projectId)
            ->whereDate('completion_date', today())
            ->whereHas('status', fn($q) => $q->where('type', 'done'))
            ->count();
    }

    /**
     * Get today's hours for this user in a project.
     */
    public function getTodayHours(int $projectId): float
    {
        return $this->dailyProgress()
            ->where('project_id', $projectId)
            ->whereDate('date', today())
            ->sum('total_hours');
    }

    /**
     * Check if user is a tester in a specific project.
     */
    public function isTesterInProject(int $projectId): bool
    {
        return $this->testerProjects()->where('project_id', $projectId)->exists();
    }

    /**
     * Check if user is an active tester in a specific project.
     */
    public function isActiveTesterInProject(int $projectId): bool
    {
        return $this->testerProjects()
            ->where('project_id', $projectId)
            ->wherePivot('status', 'active')
            ->exists();
    }
}
