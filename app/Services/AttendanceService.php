<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Project;
use App\Models\Workspace;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Mark attendance automatically when task is completed.
     */
    public function autoMarkAttendance(User $user, Project $project, float $completedHours, Carbon $date = null): Attendance
    {
        $date = $date ?? today();

        // Find or create attendance record
        $attendance = Attendance::firstOrCreate(
            [
                'workspace_id' => $project->workspace_id,
                'project_id' => $project->id,
                'guest_id' => $user->id,
                'date' => $date,
            ],
            [
                'status' => 'present',
                'completed_hours' => 0,
                'auto_marked' => 'yes',
            ]
        );

        // Update completed hours
        $attendance->completed_hours += $completedHours;
        
        // Update status based on hours
        if ($attendance->completed_hours >= 6) {
            $attendance->status = 'present';
        } elseif ($attendance->completed_hours >= 4) {
            $attendance->status = 'late';
        }

        $attendance->save();

        return $attendance;
    }

    /**
     * Check attendance by mentor.
     */
    public function mentorCheck(Attendance $attendance, User $mentor, bool $approve = true): void
    {
        $attendance->markCheckedByMentor($mentor);

        if (!$approve) {
            $attendance->update(['status' => 'absent']);
        }
    }

    /**
     * Get attendance summary for a user in a project.
     */
    public function getUserAttendanceSummary(User $user, Project $project, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = Attendance::where('guest_id', $user->id)
            ->where('project_id', $project->id);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $attendances = $query->get();

        return [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->where('status', 'present')->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'total_hours' => $attendances->sum('completed_hours'),
            'average_hours_per_day' => $attendances->avg('completed_hours'),
            'checked_by_mentor' => $attendances->where('checked_by_mentor', true)->count(),
            'pending_mentor_check' => $attendances->where('checked_by_mentor', false)->count(),
        ];
    }

    /**
     * Get attendance for a specific date.
     */
    public function getAttendanceForDate(User $user, Project $project, Carbon $date): ?Attendance
    {
        return Attendance::where('guest_id', $user->id)
            ->where('project_id', $project->id)
            ->where('date', $date)
            ->first();
    }

    /**
     * Get pending mentor checks for a project.
     */
    public function getPendingMentorChecks(Project $project): \Illuminate\Database\Eloquent\Collection
    {
        return Attendance::where('project_id', $project->id)
            ->where('checked_by_mentor', false)
            ->with(['guest', 'project'])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Bulk mentor check for multiple attendances.
     */
    public function bulkMentorCheck(array $attendanceIds, User $mentor, bool $approve = true): int
    {
        $updated = 0;

        foreach ($attendanceIds as $attendanceId) {
            $attendance = Attendance::find($attendanceId);
            
            if ($attendance && !$attendance->checked_by_mentor) {
                $this->mentorCheck($attendance, $mentor, $approve);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Get attendance report for a project.
     */
    public function getProjectAttendanceReport(Project $project, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = Attendance::where('project_id', $project->id);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $attendances = $query->with('guest')->get();

        // Group by user
        $userStats = $attendances->groupBy('guest_id')->map(function ($userAttendances, $userId) {
            $user = $userAttendances->first()->guest;
            
            return [
                'user_id' => $userId,
                'user_name' => $user->name,
                'total_days' => $userAttendances->count(),
                'present_days' => $userAttendances->where('status', 'present')->count(),
                'absent_days' => $userAttendances->where('status', 'absent')->count(),
                'late_days' => $userAttendances->where('status', 'late')->count(),
                'total_hours' => $userAttendances->sum('completed_hours'),
                'average_hours' => $userAttendances->avg('completed_hours'),
                'attendance_rate' => $userAttendances->count() > 0 
                    ? ($userAttendances->whereIn('status', ['present', 'late'])->count() / $userAttendances->count()) * 100 
                    : 0,
            ];
        });

        return [
            'project_name' => $project->name,
            'period' => [
                'start' => $startDate?->format('Y-m-d'),
                'end' => $endDate?->format('Y-m-d'),
            ],
            'total_records' => $attendances->count(),
            'user_stats' => $userStats->values()->all(),
            'overall_stats' => [
                'total_hours' => $attendances->sum('completed_hours'),
                'average_hours_per_day' => $attendances->avg('completed_hours'),
                'total_present' => $attendances->where('status', 'present')->count(),
                'total_absent' => $attendances->where('status', 'absent')->count(),
                'total_late' => $attendances->where('status', 'late')->count(),
            ],
        ];
    }

    /**
     * Check if user met attendance requirement for the day.
     */
    public function metDailyAttendance(Attendance $attendance): bool
    {
        return $attendance->meetsMinimumHours();
    }

    /**
     * Get users with poor attendance.
     */
    public function getUsersWithPoorAttendance(Project $project, int $minDays = 5, float $minAttendanceRate = 80): array
    {
        $report = $this->getProjectAttendanceReport($project);
        
        return array_filter($report['user_stats'], function ($userStat) use ($minDays, $minAttendanceRate) {
            return $userStat['total_days'] >= $minDays && $userStat['attendance_rate'] < $minAttendanceRate;
        });
    }
}
