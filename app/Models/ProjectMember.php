<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'track_id',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    // Scopes
    public function scopeGuests($query)
    {
        return $query->where('role', 'guest');
    }

    public function scopeTesters($query)
    {
        return $query->where('role', 'tester');
    }

    public function scopeMentors($query)
    {
        return $query->where('role', 'mentor');
    }

    // Helper methods
    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    public function isTester(): bool
    {
        return $this->role === 'tester';
    }

    public function isMentor(): bool
    {
        return $this->role === 'mentor';
    }
}
