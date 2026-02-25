<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceUser extends Model
{
    protected $table = 'workspace_user';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'track_id',
        'track',
        'attendance_days',
        'is_suspended',
        'absence_count',
        'created_by_user_id',
        'permissions',
        'role',
        'permissions',
        'joined_at',
    ];
}
