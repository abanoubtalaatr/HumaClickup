<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Project;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display attendance for a project.
     */
    public function index(Project $project, Request $request)
    {
        $this->authorize('view', $project);
        
        $startDate = $request->date('start_date', now()->startOfMonth());
        $endDate = $request->date('end_date', now()->endOfMonth());
        
        $report = $this->attendanceService->getProjectAttendanceReport(
            $project,
            $startDate,
            $endDate
        );
        
        return view('attendance.index', compact('project', 'report', 'startDate', 'endDate'));
    }

    /**
     * Show attendance for a specific user.
     */
    public function show(Project $project, User $user, Request $request)
    {
        $this->authorize('view', $project);
        
        $startDate = $request->date('start_date', now()->startOfMonth());
        $endDate = $request->date('end_date', now()->endOfMonth());
        
        $summary = $this->attendanceService->getUserAttendanceSummary(
            $user,
            $project,
            $startDate,
            $endDate
        );
        
        $attendances = Attendance::where('guest_id', $user->id)
            ->where('project_id', $project->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
        
        return view('attendance.show', compact('project', 'user', 'summary', 'attendances', 'startDate', 'endDate'));
    }

    /**
     * Show pending mentor checks.
     */
    public function pendingChecks(Project $project)
    {
        $this->authorize('update', $project);
        
        $pendingAttendances = $this->attendanceService->getPendingMentorChecks($project);
        
        return view('attendance.pending-checks', compact('project', 'pendingAttendances'));
    }

    /**
     * Mentor check attendance.
     */
    public function mentorCheck(Attendance $attendance, Request $request)
    {
        $this->authorize('update', $attendance->project);
        
        $request->validate([
            'approve' => 'required|boolean',
        ]);
        
        $this->attendanceService->mentorCheck(
            $attendance,
            auth()->user(),
            $request->boolean('approve')
        );
        
        return back()->with('success', 'Attendance checked successfully.');
    }

    /**
     * Bulk mentor check.
     */
    public function bulkMentorCheck(Request $request)
    {
        $request->validate([
            'attendance_ids' => 'required|array',
            'attendance_ids.*' => 'exists:attendances,id',
            'approve' => 'required|boolean',
        ]);
        
        $updated = $this->attendanceService->bulkMentorCheck(
            $request->attendance_ids,
            auth()->user(),
            $request->boolean('approve')
        );
        
        return back()->with('success', "{$updated} attendance record(s) checked successfully.");
    }

    /**
     * Show attendance calendar view.
     */
    public function calendar(Project $project, Request $request)
    {
        $this->authorize('view', $project);
        
        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $attendances = Attendance::where('project_id', $project->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('guest')
            ->get()
            ->groupBy('guest_id');
        
        $teamMembers = $project->getTeamMembers();
        
        return view('attendance.calendar', compact('project', 'attendances', 'teamMembers', 'month', 'year', 'startDate', 'endDate'));
    }
}
