<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'created_by_user_id',
        'space_id',
        'name',
        'description',
        'color',
        'icon',
        'default_assignee_id',
        'automation_rules',
        'template_id',
        'progress_calculation_method',
        'progress',
        'is_archived',
        'order',
        'start_date',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'automation_rules' => 'array',
            'progress' => 'decimal:2',
            'is_archived' => 'boolean',
            'start_date' => 'datetime',
            'due_date' => 'datetime',
        ];
    }

    // Relationships
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function defaultAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_assignee_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'template_id');
    }

    public function lists(): HasMany
    {
        return $this->hasMany(TaskList::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function customStatuses(): HasMany
    {
        return $this->hasMany(CustomStatus::class)->orderBy('order');
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(CustomField::class)->orderBy('order');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // Helper Methods
    public function calculateProgress(): void
    {
        $method = $this->progress_calculation_method;
        
        match($method) {
            'status' => $this->calculateProgressByStatus(),
            'count' => $this->calculateProgressByCount(),
            'time' => $this->calculateProgressByTime(),
            default => $this->calculateProgressByStatus(),
        };
    }

    protected function calculateProgressByStatus(): void
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks == 0) {
            $this->progress = 0;
            $this->save();
            return;
        }

        $weightedSum = $this->tasks()
            ->join('custom_statuses', function ($join) {
                $join->on('tasks.status_id', '=', 'custom_statuses.id')
                    ->where('custom_statuses.project_id', '=', $this->getKey());
            })
            ->sum('custom_statuses.progress_contribution');

        // Average contribution per task (each status 0-100), clamped to 0-100
        $this->progress = min(100, max(0, $weightedSum / $totalTasks));
        $this->save();
    }

    protected function calculateProgressByCount(): void
    {
        $total = $this->tasks()->count();
        if ($total == 0) {
            $this->progress = 0;
            return;
        }

        $done = $this->tasks()
            ->whereHas('status', fn($q) => $q->where('type', 'done'))
            ->count();

        $this->progress = min(100, max(0, ($done / $total) * 100));
        $this->save();
    }

    protected function calculateProgressByTime(): void
    {
        $estimated = $this->tasks()->sum('estimated_time');
        if ($estimated == 0) {
            $this->progress = 0;
            return;
        }

        $logged = $this->tasks()
            ->withSum('timeEntries', 'duration')
            ->get()
            ->sum('time_entries_sum_duration');

        $this->progress = min(100, max(0, ($logged / $estimated) * 100));
        $this->save();
    }

    public function getDefaultStatus(): ?CustomStatus
    {
        return $this->customStatuses()->where('is_default', true)->first()
            ?? $this->customStatuses()->orderBy('order')->first();
    }

    /**
     * Check if project is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->is_archived;
    }

    /**
     * Check if project is due soon (within specified days)
     */
    public function isDueSoon(int $days = 7): bool
    {
        if (!$this->due_date || $this->is_archived) {
            return false;
        }
        
        return $this->due_date->isFuture() && $this->due_date->lte(now()->addDays($days));
    }

    /**
     * Scope for overdue projects
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('is_archived', false);
    }

    /**
     * Scope for projects due soon
     */
    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays($days)])
            ->where('is_archived', false);
    }
}
