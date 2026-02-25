<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'workspace_id',
        'key',
        'value',
        'updated_by_user_id',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Get a setting value for the workspace (returns null if not set).
     */
    public static function getValue(int $workspaceId, string $key): ?string
    {
        $row = self::where('workspace_id', $workspaceId)->where('key', $key)->first();
        return $row ? $row->value : null;
    }

    /**
     * Set a setting value and optionally log the change.
     */
    public static function setValue(int $workspaceId, string $key, ?string $value, ?int $updatedByUserId = null): self
    {
        $row = self::firstOrNew(['workspace_id' => $workspaceId, 'key' => $key]);
        $oldValue = $row->value;
        $row->value = $value;
        $row->updated_by_user_id = $updatedByUserId;
        $row->save();

        if ($oldValue !== $value) {
            SettingAuditLog::create([
                'workspace_id' => $workspaceId,
                'setting_key' => $key,
                'old_value' => $oldValue,
                'new_value' => $value,
                'changed_by_user_id' => $updatedByUserId,
                'changed_at' => now(),
            ]);
        }

        return $row;
    }

    /** Known setting keys */
    public const KEY_ROUND_START_DATE = 'round_start_date';
}
