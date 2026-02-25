<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PullRequest extends Model
{
    protected $fillable = [
        'workspace_id',
        'user_id',
        'project_id',
        'track_id',
        'link',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Check if pull requests are required for a given date (not Friday/Saturday).
     */
    public static function isRequiredDay(\DateTimeInterface|string $date): bool
    {
        $d = $date instanceof \DateTimeInterface ? $date : \Carbon\Carbon::parse($date);
        $dayOfWeek = (int) $d->format('w'); // 0 = Sunday, 5 = Friday, 6 = Saturday
        return  $dayOfWeek !== 5 && $dayOfWeek !== 6;
    }
}
