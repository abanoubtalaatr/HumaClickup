<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'workspace_id',
        'project_id',
        'user_id',
        'date',
        'daily_progress_id',
        'status',
        'approved',
        'approved_by_user_id',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
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

    public function dailyProgress(): BelongsTo
    {
        return $this->belongsTo(DailyProgress::class, 'daily_progress_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    // Helper Methods
    
    /**
     * Check if student was present.
     */
    public function isPresent(): bool
    {
        return $this->status === 'present';
    }

    /**
     * Check if attendance is approved and locked.
     */
    public function isApproved(): bool
    {
        return $this->approved;
    }

    /**
     * Check if attendance can still be modified.
     */
    public function isLocked(): bool
    {
        return $this->approved;
    }

    /**
     * Derive attendance status from daily progress.
     * 
     * Rule: progress >= 100% → present, else absent
     */
    public static function deriveStatus(?DailyProgress $progress): string
    {
        if (!$progress) {
            return 'absent'; // No progress record = absent
        }

        return $progress->meetsTarget() ? 'present' : 'absent';
    }

    /**
     * Mark as approved by mentor.
     */
    public function approve(User $mentor, ?string $notes = null): void
    {
        if ($this->approved) {
            throw new \Exception('Attendance is already approved and cannot be modified.');
        }

        $this->update([
            'approved' => true,
            'approved_by_user_id' => $mentor->id,
            'approved_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Update status based on current progress (only if not locked).
     */
    public function updateFromProgress(): void
    {
        if ($this->isLocked()) {
            throw new \Exception('Cannot update approved attendance.');
        }

        $this->status = self::deriveStatus($this->dailyProgress);
        $this->save();
    }
}
