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
        'group_id',
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
        'total_days',
        'working_days',
        'exclude_weekends',
        'required_main_tasks_count',
        'current_main_tasks_count',
        'min_task_hours',
        'bug_time_allocation_percentage',
        'weekly_hours_target',
        'tasks_requirement_met',
        'start_date',
        'due_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'automation_rules' => 'array',
            'progress' => 'decimal:2',
            'is_archived' => 'boolean',
            'exclude_weekends' => 'boolean',
            'tasks_requirement_met' => 'boolean',
            'total_days' => 'integer',
            'working_days' => 'integer',
            'required_main_tasks_count' => 'integer',
            'current_main_tasks_count' => 'integer',
            'min_task_hours' => 'decimal:2',
            'bug_time_allocation_percentage' => 'decimal:2',
            'weekly_hours_target' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
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

    public function testers(): HasMany
    {
        return $this->hasMany(ProjectTester::class);
    }

    public function activeTesters(): HasMany
    {
        return $this->testers()->where('status', 'active');
    }

    public function dailyProgress(): HasMany
    {
        return $this->hasMany(DailyProgress::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
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

    /**
     * Check if project meets task requirements.
     */
    public function meetsTaskRequirements(): bool
    {
        return $this->current_main_tasks_count >= $this->required_main_tasks_count;
    }

    /**
     * Update main tasks count.
     */
    public function updateMainTasksCount(): void
    {
        $count = $this->tasks()->where('is_main_task', 'yes')->count();
        $this->update([
            'current_main_tasks_count' => $count,
            'tasks_requirement_met' => $count >= $this->required_main_tasks_count
        ]);
    }

    /**
     * Calculate required main tasks based on group size and working days.
     */
    public function calculateRequiredMainTasks(): int
    {
        if (!$this->group) {
            return 0;
        }
        
        $groupMembersCount = $this->group->guests()->count();
        return $groupMembersCount * $this->working_days;
    }

    /**
     * Calculate working days excluding weekends (Friday & Saturday).
     */
    public static function calculateWorkingDays(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, bool $excludeWeekends = true): int
    {
        if (!$excludeWeekends) {
            return $startDate->diffInDays($endDate) + 1;
        }

        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Skip Friday (5) and Saturday (6)
            if (!in_array($currentDate->dayOfWeek, [5, 6])) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    /**
     * Get team members from the assigned group.
     */
    public function getTeamMembers()
    {
        return $this->group ? $this->group->guests : collect();
    }

    /**
     * Get members without tasks.
     */
    public function getMembersWithoutTasks()
    {
        $teamMembers = $this->getTeamMembers();
        return $teamMembers->filter(function ($member) {
            return $this->tasks()->whereHas('assignees', function ($q) use ($member) {
                $q->where('users.id', $member->id);
            })->count() === 0;
        });
    }

    /**
     * Get members with overdue tasks.
     */
    public function getMembersWithOverdueTasks()
    {
        $teamMembers = $this->getTeamMembers();
        return $teamMembers->filter(function ($member) {
            return $this->tasks()
                ->whereHas('assignees', function ($q) use ($member) {
                    $q->where('users.id', $member->id);
                })
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->whereHas('status', fn($q) => $q->where('type', '!=', 'done'))
                ->count() > 0;
        });
    }

    /**
     * Get members not meeting weekly hours target.
     */
    public function getMembersNotMeetingWeeklyTarget()
    {
        return $this->getTeamMembers()->filter(function ($member) {
            return !$member->meets_weekly_target;
        });
    }
}
