<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = [
        'workspace_id',
        'created_by_user_id',
        'name',
        'description',
        'color',
        'whatsapp_link',
        'slack_link',
        'repo_link',
        'service_link',
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
     * Get the guests assigned to this group.
     */
    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    /**
     * Check if a user is in this group.
     */
    public function hasGuest(User $user): bool
    {
        return $this->guests()->where('user_id', $user->id)->exists();
    }
}
