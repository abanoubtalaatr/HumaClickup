<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Sprint extends Model
{
    protected $fillable = [
        'workspace_id',
        'project_id',
        'name',
        'goal',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    // Relationships
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Helper Methods
    
    /**
     * Get sprint duration in days
     */
    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Get days remaining in sprint
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            return 0;
        }
        
        if (now()->lt($this->start_date)) {
            return $this->start_date->diffInDays(now());
        }
        
        if (now()->gt($this->end_date)) {
            return 0;
        }
        
        return now()->diffInDays($this->end_date);
    }

    /**
     * Get percentage of time elapsed
     */
    public function getTimeProgressAttribute(): float
    {
        if (now()->lt($this->start_date)) {
            return 0;
        }
        
        if (now()->gt($this->end_date)) {
            return 100;
        }
        
        $totalDays = $this->start_date->diffInDays($this->end_date);
        $elapsedDays = $this->start_date->diffInDays(now());
        
        return $totalDays > 0 ? ($elapsedDays / $totalDays) * 100 : 0;
    }

    /**
     * Get completion percentage based on tasks
     */
    public function getCompletionProgressAttribute(): float
    {
        $totalTasks = $this->tasks()->count();
        
        if ($totalTasks === 0) {
            return 0;
        }
        
        $completedTasks = $this->tasks()
            ->whereHas('status', fn($q) => $q->where('type', 'done'))
            ->count();
        
        return ($completedTasks / $totalTasks) * 100;
    }

    /**
     * Check if sprint is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && now()->between($this->start_date, $this->end_date);
    }

    /**
     * Check if sprint is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'active' && now()->gt($this->end_date);
    }

    /**
     * Check if sprint is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->status === 'planning' && now()->lt($this->start_date);
    }

    /**
     * Scope for active sprints
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed sprints
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for current sprints (active and within date range)
     */
    public function scopeCurrent($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }
}
