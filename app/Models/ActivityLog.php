<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'workspace_id',
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    // Relationships
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // Helper Methods
    public function getDescription(): string
    {
        $subjectName = class_basename($this->subject_type);
        
        return match($this->action) {
            'created' => "created {$subjectName}",
            'updated' => "updated {$subjectName}",
            'deleted' => "deleted {$subjectName}",
            'assigned' => "assigned {$subjectName}",
            'status_changed' => "changed status of {$subjectName}",
            default => "performed {$this->action} on {$subjectName}",
        };
    }
}
