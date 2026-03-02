<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DailyProgress extends Model
{
    protected $table = 'daily_progress';

    protected $fillable = [
        'workspace_id',
        'project_id',
        'user_id',
        'date',
        'task_id',
        'required_hours',
        'completed_hours',
        'progress_percentage',
        'approved',
        'approved_by_user_id',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'required_hours' => 'decimal:2',
            'completed_hours' => 'decimal:2',
            'progress_percentage' => 'decimal:2',
            'approved' => 'boolean',
            'approved_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class, 'daily_progress_id');
    }

    // Helper Methods
    
    /**
     * Check if this progress meets the daily target (100%).
     */
    public function meetsTarget(): bool
    {
        return $this->progress_percentage >= 100;
    }

    /**
     * Check if progress is approved and locked.
     */
    public function isApproved(): bool
    {
        return (bool) $this->approved;
    }

    /**
     * Check if progress can still be modified.
     */
    public function isLocked(): bool
    {
        return $this->approved;
    }

    /**
     * Mark as approved by mentor.
     */
    public function approve(User $mentor): void
    {
        if ($this->approved) {
            throw new \Exception('Progress is already approved and cannot be modified.');
        }

        $this->update([
            'approved' => true,
            'approved_by_user_id' => $mentor->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Calculate progress percentage from completed/required hours.
     */
    public function calculateProgress(): float
    {
        if ($this->required_hours == 0) {
            return 0;
        }

        $percentage = ($this->completed_hours / $this->required_hours) * 100;
        
        // Cap at 100%
        return min($percentage, 100);
    }

    /**
     * Update progress metrics (for service layer).
     * Clamps to valid DB range: completed_hours 0..999.99, progress 0..100.
     */
    public function updateProgress(float $completedHours): void
    {
        if ($this->isLocked()) {
            throw new \Exception('Cannot update approved progress.');
        }

        $completedHours = round(min(max($completedHours, 0), 999.99), 2);
        $this->completed_hours = $completedHours;
        $this->progress_percentage = $this->calculateProgress();
        $this->save();
    }
}
