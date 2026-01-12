<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    protected $fillable = [
        'workspace_id',
        'task_id',
        'user_id',
        'start_time',
        'end_time',
        'duration',
        'description',
        'is_billable',
        'is_manual',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    // Relationships
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('end_time');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('end_time');
    }

    public function scopeBillable(Builder $query): Builder
    {
        return $query->where('is_billable', true);
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->end_time === null;
    }

    public function calculateDuration(): ?int
    {
        if (!$this->end_time) {
            return null;
        }
        
        return $this->start_time->diffInSeconds($this->end_time);
    }

    public function stop(): void
    {
        $this->end_time = now();
        $this->duration = $this->calculateDuration();
        $this->save();
    }

    public function getFormattedDuration(): string
    {
        if (!$this->duration) {
            return '0m';
        }
        
        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }
}
