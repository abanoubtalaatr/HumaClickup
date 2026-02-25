<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workspace extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'description',
        'settings',
        'billing_status',
        'storage_limit',
        'storage_used',
        'member_capacity',
        'custom_branding',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'custom_branding' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    // Relationships
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role', 'permissions', 'joined_at', 'track', 'track_id', 'created_by_user_id')
            ->withTimestamps();
    }

    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class,'pivot_track_id');
    }
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    // Helper Methods
    public function addMember(User $user, string $role = 'member', ?int $trackId = null, ?int $createdByUserId = null, ?array $attendanceDays = null): void
    {
        $this->users()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
            'track_id' => $trackId,
            'created_by_user_id' => $createdByUserId,
            'attendance_days' => $attendanceDays ? json_encode($attendanceDays) : null,
        ]);
    }

    public function removeMember(User $user): void
    {
        $this->users()->detach($user->id);
    }

    public function updateMemberRole(User $user, string $role, ?int $trackId = null): void
    {
        $data = ['role' => $role];
        if ($trackId !== null) {
            $data['track_id'] = $trackId;
        }
        $this->users()->updateExistingPivot($user->id, $data);
    }

    /**
     * Get members (non-guests) in the workspace
     */
    public function members(): BelongsToMany
    {
        return $this->users()->wherePivotIn('role', ['owner', 'admin', 'member']);
    }

    /**
     * Get guests in the workspace
     */
    public function guests(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'guest');
    }

    /**
     * Get guests created by a specific user
     */
    public function guestsCreatedBy(int $userId): BelongsToMany
    {
        return $this->users()
            ->wherePivot('role', 'guest')
            ->wherePivot('created_by_user_id', $userId);
    }

    /**
     * Get all admins (including owner)
     */
    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivotIn('role', ['owner', 'admin']);
    }

    public function getStorageUsagePercentage(): float
    {
        if ($this->storage_limit == 0) {
            return 0;
        }
        return ($this->storage_used / $this->storage_limit) * 100;
    }

    public function isStorageLimitReached(): bool
    {
        return $this->storage_used >= $this->storage_limit;
    }
}
