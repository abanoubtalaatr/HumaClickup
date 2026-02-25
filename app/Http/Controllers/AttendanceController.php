<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\AbsenceTrackingService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;
    protected AbsenceTrackingService $absenceTrackingService;

    public function __construct(AttendanceService $attendanceService, AbsenceTrackingService $absenceTrackingService)
    {
        $this->attendanceService = $attendanceService;
        $this->absenceTrackingService = $absenceTrackingService;
    }

    /**
     * Display attendance (absence tracking) dashboard for the workspace.
     * Absence is calculated from overdue tasks: incomplete past start_date = absence days.
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }


        $asOfDate = $request->input('as_of', Carbon::today());
        
        $asOfDate = Carbon::parse($asOfDate instanceof \DateTimeInterface ? $asOfDate->format('Y-m-d') : $asOfDate)->startOfDay();
        
        if ($asOfDate->isFuture()) {
            $asOfDate = Carbon::today()->startOfDay();
        }

        $user = auth()->user();
        $guestIdsFilter = null;
        if ($user->isMemberOnlyInWorkspace($workspaceId)) {
            $myGuests = $user->getCreatedGuestsInWorkspace($workspaceId);
            $guestIdsFilter = $myGuests->pluck('id')->toArray();
            // Also include guests assigned to tasks in projects created by this member
            $myProjectIds = Project::where('workspace_id', $workspaceId)
                ->where('created_by_user_id', $user->id)
                ->pluck('id');
            if ($myProjectIds->isNotEmpty()) {
                $taskIds = Task::withoutGlobalScopes()
                    ->whereIn('project_id', $myProjectIds)
                    ->pluck('id');
                if ($taskIds->isNotEmpty()) {
                    $assigneeIds = DB::table('task_assignees')
                        ->whereIn('task_id', $taskIds)
                        ->pluck('user_id')
                        ->unique()
                        ->values();
                    $guestIdsFromTasks = User::whereIn('id', $assigneeIds)
                        ->whereHas('workspaces', fn ($q) => $q->where('workspace_id', $workspaceId)->where('role', 'guest'))
                        ->pluck('id')
                        ->toArray();
                    $guestIdsFilter = array_values(array_unique(array_merge($guestIdsFilter, $guestIdsFromTasks)));
                }
            }
        }

        
        $summary = $this->absenceTrackingService->getAbsenceSummaryForWorkspace($workspaceId, $asOfDate, $guestIdsFilter);
        

        return view('attendance.index', compact('summary', 'asOfDate'));
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
