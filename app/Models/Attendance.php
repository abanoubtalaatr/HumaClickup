<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'workspace_id',
        'project_id',
        'guest_id',
        'date',
        'checked_in_at',
        'checked_out_at',
        'status',
        'completed_hours',
        'checked_by_mentor',
        'mentor_id',
        'mentor_checked_at',
        'auto_marked',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'mentor_checked_at' => 'datetime',
        'checked_by_mentor' => 'boolean',
        'completed_hours' => 'decimal:2',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    // Check if guest checked in
    public function hasCheckedIn(): bool
    {
        return !is_null($this->checked_in_at);
    }

    // Check if guest checked out
    public function hasCheckedOut(): bool
    {
        return !is_null($this->checked_out_at);
    }

    // Get total hours attended
    public function getTotalHoursAttribute(): ?float
    {
        if ($this->checked_in_at && $this->checked_out_at) {
            $checkIn = Carbon::parse($this->checked_in_at);
            $checkOut = Carbon::parse($this->checked_out_at);
            return $checkOut->diffInHours($checkIn, true);
        }
        return null;
    }

    // Check if attended early (before 9:00 AM)
    public function getAttendedEarlyAttribute(): bool
    {
        if (!$this->checked_in_at) {
            return false;
        }
        $checkInTime = Carbon::parse($this->checked_in_at);
        $earlyThreshold = Carbon::parse('09:00:00');
        return $checkInTime->lessThanOrEqualTo($earlyThreshold);
    }

    // Check if attended full time (at least 6 hours)
    public function getAttendedFullTimeAttribute(): bool
    {
        if (!$this->total_hours) {
            return false;
        }
        return $this->total_hours >= 6;
    }

    // Get attendance quality indicator
    public function getQualityIndicatorAttribute(): string
    {
        if ($this->status === 'absent') {
            return 'absent';
        }
        
        if (!$this->hasCheckedOut()) {
            return 'in_progress';
        }

        if ($this->attended_full_time && $this->attended_early) {
            return 'excellent';
        }
        
        if ($this->attended_full_time) {
            return 'good';
        }
        
        if ($this->total_hours >= 4) {
            return 'fair';
        }
        
        return 'poor';
    }

    // Scopes
    public function scopeForWorkspace($query, int $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopeForGuest($query, int $guestId)
    {
        return $query->where('guest_id', $guestId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('date', now()->year)
                     ->whereMonth('date', now()->month);
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopePendingMentorCheck($query)
    {
        return $query->where('checked_by_mentor', false);
    }

    public function scopeAutoMarked($query)
    {
        return $query->where('auto_marked', 'yes');
    }

    /**
     * Check if attendance meets the minimum hours requirement.
     */
    public function meetsMinimumHours(): bool
    {
        return $this->completed_hours >= 6;
    }

    /**
     * Mark as checked by mentor.
     */
    public function markCheckedByMentor(User $mentor): void
    {
        $this->update([
            'checked_by_mentor' => true,
            'mentor_id' => $mentor->id,
            'mentor_checked_at' => now(),
        ]);
    }
}
