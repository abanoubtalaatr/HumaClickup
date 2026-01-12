<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomStatus extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'color',
        'type',
        'order',
        'progress_contribution',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'progress_contribution' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'status_id');
    }

    // Helper Methods
    public function canDelete(): bool
    {
        return $this->tasks()->count() === 0;
    }
}
