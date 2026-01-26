<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'project_id',
        'sprint_id',
        'list_id',
        'status_id',
        'creator_id',
        'parent_id',
        'related_task_id',
        'title',
        'type',
        'description',
        'priority',
        'due_date',
        'start_date',
        'estimated_time',
        'estimated_minutes',
        'estimation_status',
        'estimation_completed_at',
        'estimation_edited_by',
        'position',
        'completion_percentage',
        'recurring_settings',
        'is_archived',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'start_date' => 'datetime',
            'recurring_settings' => 'array',
            'completion_percentage' => 'decimal:2',
            'is_archived' => 'boolean',
            'is_private' => 'boolean',
            'estimated_minutes' => 'integer',
            'estimation_completed_at' => 'datetime',
            'type' => 'string',
        ];
    }

    // Global Scope for workspace isolation
    protected static function booted(): void
    {
        static::addGlobalScope('workspace', function (Builder $builder) {
            if (auth()->check() && session('current_workspace_id')) {
                $builder->where('workspace_id', session('current_workspace_id'));
            }
        });
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

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CustomStatus::class, 'status_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function relatedTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'related_task_id');
    }

    public function bugs(): HasMany
    {
        return $this->hasMany(Task::class, 'related_task_id')->where('type', 'bug')->orderBy('position');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('position');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees')->withTimestamps();
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_watchers')->withTimestamps();
    }

    public function blockingTasks(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'depends_on_task_id',
            'task_id'
        )->withPivot('type');
    }

    public function blockedByTasks(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'task_id',
            'depends_on_task_id'
        )->withPivot('type');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class, 'customizable_id')
            ->where('customizable_type', self::class);
    }

    public function estimations(): HasMany
    {
        return $this->hasMany(TaskEstimation::class);
    }

    public function estimationEditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estimation_edited_by');
    }

    // Scopes
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
            ->whereHas('status', fn($q) => $q->where('type', '!=', 'done'));
    }

    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereBetween('due_date', [now(), now()->addDays($days)])
            ->whereHas('status', fn($q) => $q->where('type', '!=', 'done'));
    }

    public function scopeBlocked(Builder $query): Builder
    {
        return $query->whereHas('blockedByTasks', function ($q) {
            $q->whereHas('status', fn($sq) => $sq->where('type', '!=', 'done'));
        });
    }

    public function scopeBugs(Builder $query): Builder
    {
        return $query->where('type', 'bug');
    }

    public function scopeTasks(Builder $query): Builder
    {
        return $query->where('type', 'task');
    }

    // Helper Methods
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() 
            && $this->status?->type !== 'done';
    }

    public function isBlocked(): bool
    {
        return $this->blockedByTasks()
            ->whereHas('status', fn($q) => $q->where('type', '!=', 'done'))
            ->exists();
    }

    public function getTotalTimeLogged(): int
    {
        return $this->timeEntries()
            ->whereNotNull('end_time')
            ->sum('duration') ?? 0;
    }

    public function getFormattedTimeLogged(): string
    {
        $minutes = (int)($this->getTotalTimeLogged() / 60);
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        }
        return "{$mins}m";
    }

    public function getSubtaskProgress(): array
    {
        $subtasks = $this->subtasks;
        $total = $subtasks->count();
        $completed = $subtasks->where('status.type', 'done')->count();
        
        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? ($completed / $total) * 100 : 0,
        ];
    }

    // Estimation Helper Methods
    
    /**
     * Get the estimation submitted by a specific user
     */
    public function getEstimationByUser(int $userId): ?TaskEstimation
    {
        return $this->estimations()->where('user_id', $userId)->first();
    }

    /**
     * Check if a user has submitted an estimation
     */
    public function hasEstimationFromUser(int $userId): bool
    {
        return $this->estimations()->where('user_id', $userId)->exists();
    }

    /**
     * Get guest assignees who need to submit estimations
     */
    public function getGuestAssignees(): \Illuminate\Database\Eloquent\Collection
    {
        $workspaceId = $this->workspace_id;
        
        return $this->assignees()
            ->whereHas('workspaces', function ($q) use ($workspaceId) {
                $q->where('workspaces.id', $workspaceId)
                  ->where('workspace_user.role', 'guest');
            })
            ->get();
    }

    /**
     * Check if all guest assignees have submitted estimations
     */
    public function allGuestsHaveEstimated(): bool
    {
        $guestAssignees = $this->getGuestAssignees();
        
        if ($guestAssignees->isEmpty()) {
            return false;
        }

        $estimationCount = $this->estimations()
            ->whereIn('user_id', $guestAssignees->pluck('id'))
            ->count();

        return $estimationCount >= $guestAssignees->count();
    }

    /**
     * Calculate and set the average estimation from all guest submissions
     */
    public function calculateAverageEstimation(): ?int
    {
        $guestAssignees = $this->getGuestAssignees();
        
        if ($guestAssignees->isEmpty()) {
            return null;
        }

        $estimations = $this->estimations()
            ->whereIn('user_id', $guestAssignees->pluck('id'))
            ->get();

        if ($estimations->isEmpty()) {
            return null;
        }

        return (int) round($estimations->avg('estimated_minutes'));
    }

    /**
     * Complete the estimation polling and set the final estimate
     */
    public function completeEstimationPolling(): bool
    {
        if (!$this->allGuestsHaveEstimated()) {
            return false;
        }

        $averageMinutes = $this->calculateAverageEstimation();

        $this->update([
            'estimated_minutes' => $averageMinutes,
            'estimation_status' => 'completed',
            'estimation_completed_at' => now(),
        ]);

        return true;
    }

    /**
     * Start estimation polling for this task
     */
    public function startEstimationPolling(): void
    {
        if ($this->estimation_status === 'pending') {
            $this->update(['estimation_status' => 'polling']);
        }
    }

    /**
     * Get estimation polling progress
     */
    public function getEstimationProgress(): array
    {
        $guestAssignees = $this->getGuestAssignees();
        $totalGuests = $guestAssignees->count();
        
        $submittedCount = $this->estimations()
            ->whereIn('user_id', $guestAssignees->pluck('id'))
            ->count();

        return [
            'total' => $totalGuests,
            'submitted' => $submittedCount,
            'remaining' => $totalGuests - $submittedCount,
            'percentage' => $totalGuests > 0 ? round(($submittedCount / $totalGuests) * 100) : 0,
            'is_complete' => $submittedCount >= $totalGuests && $totalGuests > 0,
        ];
    }

    /**
     * Check if task needs estimation (has guest assignees and estimation not completed)
     */
    public function needsEstimation(): bool
    {
        return $this->estimation_status !== 'completed' 
            && $this->getGuestAssignees()->isNotEmpty();
    }

    /**
     * Get formatted estimation time
     */
    public function getFormattedEstimation(): ?string
    {
        if (!$this->estimated_minutes) {
            return null;
        }

        $hours = floor($this->estimated_minutes / 60);
        $minutes = $this->estimated_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }
}
