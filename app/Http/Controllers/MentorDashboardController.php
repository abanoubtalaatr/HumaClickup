<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\DailyProgress;
use App\Models\Attendance;
use App\Services\DailyProgressService;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MentorDashboardController extends Controller
{
    protected DailyProgressService $progressService;
    protected AttendanceService $attendanceService;

    public function __construct(
        DailyProgressService $progressService,
        AttendanceService $attendanceService
    ) {
        $this->progressService = $progressService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Show mentor dashboard with pending approvals.
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Only mentors/admins/owners can access
        if (!$user->isMemberInWorkspace($workspaceId) && 
            !$user->isAdminInWorkspace($workspaceId) && 
            !$user->isOwnerInWorkspace($workspaceId)) {
            abort(403, 'Only mentors can access this dashboard.');
        }

        $date = $request->date('date', today());

        // Get all projects in workspace
        $projects = Project::where('workspace_id', $workspaceId)
            ->where('is_archived', false)
            ->get();

        // Get pending progress approvals for the date
        $pendingProgress = collect();
        $pendingAttendance = collect();

        foreach ($projects as $project) {
            $pendingProgress = $pendingProgress->merge(
                $this->progressService->getPendingApprovals($project, $date)
            );
            $pendingAttendance = $pendingAttendance->merge(
                $this->attendanceService->getPendingApprovals($project, $date)
            );
        }

        // Get guests without main tasks
        $guestsWithoutTasks = collect();
        foreach ($projects as $project) {
            $guests = $this->progressService->getGuestsWithoutMainTask($project, $date);
            foreach ($guests as $guest) {
                $guestsWithoutTasks->push([
                    'guest' => $guest,
                    'project' => $project,
                ]);
            }
        }

        // Get guests with incomplete progress
        $guestsWithIncomplete = collect();
        foreach ($projects as $project) {
            $guests = $this->progressService->getGuestsWithIncompleteProgress($project, $date);
            foreach ($guests as $guest) {
                $guestsWithIncomplete->push([
                    'guest' => $guest,
                    'project' => $project,
                ]);
            }
        }

        return view('mentor.dashboard', compact(
            'pendingProgress',
            'pendingAttendance',
            'guestsWithoutTasks',
            'guestsWithIncomplete',
            'date'
        ));
    }

    /**
     * Approve daily progress.
     */
    public function approveProgress(Request $request, DailyProgress $progress)
    {
        $user = auth()->user();

        // Authorization check
        if ($progress->workspace_id != session('current_workspace_id')) {
            abort(403);
        }

        try {
            $progress->approve($user);

            // Also derive and update attendance
            $this->attendanceService->deriveAttendanceFromProgress($progress);

            return back()->with('success', 'Progress approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve attendance.
     */
    public function approveAttendance(Request $request, Attendance $attendance)
    {
        $user = auth()->user();

        // Authorization check
        if ($attendance->workspace_id != session('current_workspace_id')) {
            abort(403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->attendanceService->approveAttendance(
                $attendance, 
                $user, 
                $validated['notes'] ?? null
            );

            return back()->with('success', 'Attendance approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk approve progress records.
     */
    public function bulkApproveProgress(Request $request)
    {
        $validated = $request->validate([
            'progress_ids' => 'required|array',
            'progress_ids.*' => 'exists:daily_progress,id',
        ]);

        $user = auth()->user();
        $count = 0;

        foreach ($validated['progress_ids'] as $progressId) {
            $progress = DailyProgress::find($progressId);

            if ($progress && !$progress->isApproved()) {
                try {
                    $progress->approve($user);
                    $this->attendanceService->deriveAttendanceFromProgress($progress);
                    $count++;
                } catch (\Exception $e) {
                    // Continue with next item
                }
            }
        }

        return back()->with('success', "{$count} progress record(s) approved.");
    }

    /**
     * Bulk approve attendance records.
     */
    public function bulkApproveAttendance(Request $request)
    {
        $validated = $request->validate([
            'attendance_ids' => 'required|array',
            'attendance_ids.*' => 'exists:attendances,id',
        ]);

        $user = auth()->user();
        $count = $this->attendanceService->bulkApprove($validated['attendance_ids'], $user);

        return back()->with('success', "{$count} attendance record(s) approved.");
    }

    /**
     * Show guest progress detail.
     */
    public function showGuestProgress(Request $request, Project $project, $userId)
    {
        $guest = \App\Models\User::findOrFail($userId);
        $date = $request->date('date', today());

        // Get daily progress
        $dailyProgress = DailyProgress::where('user_id', $guest->id)
            ->where('project_id', $project->id)
            ->where('date', $date)
            ->first();

        // Get weekly progress
        $weekStart = $date->copy()->startOfWeek();
        $weeklyProgress = $this->progressService->calculateWeeklyProgress($guest, $project, $weekStart);

        // Get attendance
        $attendance = $this->attendanceService->getAttendance($guest, $project, $date);

        return view('mentor.guest-progress', compact(
            'guest',
            'project',
            'date',
            'dailyProgress',
            'weeklyProgress',
            'attendance'
        ));
    }
}
