<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyProgress extends Model
{
    protected $table = 'daily_progress';

    protected $fillable = [
        'workspace_id',
        'project_id',
        'user_id',
        'date',
        'completed_tasks_count',
        'total_hours',
        'progress_percentage',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'completed_tasks_count' => 'integer',
        'total_hours' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
    ];

    /**
     * Get the workspace.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for workspace.
     */
    public function scopeForWorkspace($query, int $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope for project.
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope for user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope for this week.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope for this month.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('date', now()->year)
                     ->whereMonth('date', now()->month);
    }

    /**
     * Check if the user met their daily target.
     */
    public function metDailyTarget(): bool
    {
        return $this->total_hours >= 6 && $this->completed_tasks_count >= 1;
    }

    /**
     * Update progress with new completed task.
     */
    public function incrementCompletedTasks(float $hours): void
    {
        $this->increment('completed_tasks_count');
        $this->increment('total_hours', $hours);
        $this->recalculateProgress();
    }

    /**
     * Recalculate progress percentage.
     */
    public function recalculateProgress(): void
    {
        // Progress is based on completing at least 1 task with 6+ hours
        $baseProgress = ($this->completed_tasks_count > 0) ? 50 : 0;
        $hoursProgress = min(50, ($this->total_hours / 6) * 50);
        
        $this->update([
            'progress_percentage' => min(100, $baseProgress + $hoursProgress)
        ]);
    }
}
