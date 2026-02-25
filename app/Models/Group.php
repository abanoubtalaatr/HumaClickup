<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = [
        'workspace_id',
        'track_id',
        'created_by_user_id',
        'name',
        'description',
        'color',
        'min_members',
        'max_members',
        'is_active',
        'whatsapp_link',
        'slack_link',
        'repo_link',
        'service_link',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_members' => 'integer',
        'max_members' => 'integer',
    ];

    /**
     * Get the workspace that owns the group.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the user who created the group.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the track that this group belongs to.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get the guests assigned to this group.
     */
    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withPivot('role', 'assigned_by_user_id', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Get the group leader(s).
     */
    public function leaders(): BelongsToMany
    {
        return $this->guests()->wherePivot('role', 'leader');
    }

    /**
     * Get the group members (non-leaders).
     */
    public function members(): BelongsToMany
    {
        return $this->guests()->wherePivot('role', 'member');
    }

    /**
     * Check if a user is in this group.
     */
    public function hasGuest(User $user): bool
    {
        return $this->guests()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if group is full.
     */
    public function isFull(): bool
    {
        return $this->guests()->count() >= $this->max_members;
    }

    /**
     * Check if group meets minimum members requirement.
     */
    public function meetsMinimum(): bool
    {
        return $this->guests()->count() >= $this->min_members;
    }

    /**
     * Get the number of available slots.
     */
    public function availableSlots(): int
    {
        return max(0, $this->max_members - $this->guests()->count());
    }
}
