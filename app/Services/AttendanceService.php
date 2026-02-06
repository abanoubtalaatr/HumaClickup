<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DailyProgress;
use App\Models\User;
use App\Models\Project;
use Carbon\Carbon;

/**
 * AttendanceService
 * 
 * Service for managing attendance DERIVED from daily progress.
 * 
 * Business Rules:
 * - Attendance is NEVER manually set
 * - Status is derived: progress >= 100% → present, else → absent
 * - No main task = absent
 * - Incomplete task = absent
 * - Attendance requires mentor approval to lock
 */
class AttendanceService
{
    /**
     * Derive and create/update attendance from daily progress.
     * 
     * @param DailyProgress $progress
     * @return Attendance
     */
    public function deriveAttendanceFromProgress(DailyProgress $progress): Attendance
    {
        // Derive status from progress
        $status = $progress->meetsTarget() ? 'present' : 'absent';

        // Find or create attendance record
        $attendance = Attendance::firstOrCreate(
            [
                'workspace_id' => $progress->workspace_id,
                'project_id' => $progress->project_id,
                'user_id' => $progress->user_id,
                'date' => $progress->date,
            ],
            [
                'daily_progress_id' => $progress->id,
                'status' => $status,
                'approved' => false,
            ]
        );

        // Update status if not locked (not approved)
        if (!$attendance->isApproved()) {
            $attendance->update([
                'daily_progress_id' => $progress->id,
                'status' => $status,
            ]);
        }

        return $attendance;
    }

    /**
     * Get attendance for a specific date.
     * 
     * @param User $guest
     * @param Project $project
     * @param Carbon $date
     * @return Attendance|null
     */
    public function getAttendance(User $guest, Project $project, Carbon $date): ?Attendance
    {
        return Attendance::where('user_id', $guest->id)
            ->where('project_id', $project->id)
            ->where('date', $date)
            ->first();
    }

    /**
     * Get attendance summary for a guest in a project.
     * 
     * @param User $guest
     * @param Project $project
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getAttendanceSummary(User $guest, Project $project, Carbon $startDate, Carbon $endDate): array
    {
        $attendances = Attendance::where('user_id', $guest->id)
            ->where('project_id', $project->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Filter out weekends (Friday & Saturday)
        $workingDays = $attendances->filter(function ($attendance) {
            $dayOfWeek = $attendance->date->dayOfWeek;
            return !in_array($dayOfWeek, [5, 6]);
        });

        $presentCount = $workingDays->where('status', 'present')->count();
        $absentCount = $workingDays->where('status', 'absent')->count();
        $totalDays = $workingDays->count();
        $attendanceRate = $totalDays > 0 ? ($presentCount / $totalDays) * 100 : 0;

        return [
            'guest' => $guest->name,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'total_working_days' => $totalDays,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'attendance_rate' => round($attendanceRate, 2),
            'approved_count' => $workingDays->where('approved', true)->count(),
            'pending_approval_count' => $workingDays->where('approved', false)->count(),
        ];
    }

    /**
     * Get all attendance records pending approval for a project.
     * 
     * @param Project $project
     * @param Carbon|null $date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingApprovals(Project $project, ?Carbon $date = null)
    {
        $query = Attendance::where('project_id', $project->id)
            ->where('approved', false)
            ->with(['user', 'dailyProgress']);

        if ($date) {
            $query->where('date', $date);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    /**
     * Approve attendance (locks it).
     * 
     * @param Attendance $attendance
     * @param User $mentor
     * @param string|null $notes
     * @return void
     */
    public function approveAttendance(Attendance $attendance, User $mentor, ?string $notes = null): void
    {
        $attendance->approve($mentor, $notes);
    }

    /**
     * Bulk approve attendances.
     * 
     * @param array $attendanceIds
     * @param User $mentor
     * @return int Number of approved records
     */
    public function bulkApprove(array $attendanceIds, User $mentor): int
    {
        $count = 0;

        foreach ($attendanceIds as $attendanceId) {
            $attendance = Attendance::find($attendanceId);
            
            if ($attendance && !$attendance->isApproved()) {
                $this->approveAttendance($attendance, $mentor);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get guests with poor attendance in a project.
     * 
     * @param Project $project
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param float $thresholdPercentage
     * @return \Illuminate\Support\Collection
     */
    public function getGuestsWithPoorAttendance(
        Project $project,
        Carbon $startDate,
        Carbon $endDate,
        float $thresholdPercentage = 80
    ) {
        $guests = $project->getGuestMembers();

        return $guests->filter(function ($guest) use ($project, $startDate, $endDate, $thresholdPercentage) {
            $summary = $this->getAttendanceSummary($guest, $project, $startDate, $endDate);
            return $summary['attendance_rate'] < $thresholdPercentage;
        });
    }
}
