<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance page based on user role
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }

        // Admin view - see all guests and manage attendance
        if ($user->isAdminInWorkspace($workspaceId) || $user->isOwnerInWorkspace($workspaceId)) {
            return $this->adminView($workspaceId, $request);
        }
        
        // Member view - see their guests' attendance
        if ($user->isMemberOnlyInWorkspace($workspaceId)) {
            return $this->memberView($workspaceId, $user, $request);
        }
        
        // Guest view - see their own attendance
        return $this->guestView($workspaceId, $user);
    }

    /**
     * Guest view - see own attendance
     */
    private function guestView(int $workspaceId, User $guest)
    {
        $workspace = Workspace::find($workspaceId);
        
        // Get guest's attendance days
        $attendanceDays = $workspace->users()
            ->where('user_id', $guest->id)
            ->first()
            ->pivot
            ->attendance_days ?? [];
        
        $attendanceDays = is_string($attendanceDays) ? json_decode($attendanceDays, true) : $attendanceDays;

        // Get attendance records for current month
        $attendances = Attendance::forWorkspace($workspaceId)
            ->forGuest($guest->id)
            ->thisMonth()
            ->orderBy('date', 'desc')
            ->get();

        // Get today's attendance
        $todayAttendance = Attendance::forWorkspace($workspaceId)
            ->forGuest($guest->id)
            ->forDate(now())
            ->first();

        // Check if today is an attendance day
        $todayDayName = strtolower(now()->format('l'));
        $shouldAttendToday = in_array($todayDayName, $attendanceDays ?? []);

        return view('attendance.guest', compact(
            'attendances', 
            'todayAttendance', 
            'shouldAttendToday', 
            'attendanceDays'
        ));
    }

    /**
     * Member view - see their guests' attendance
     */
    private function memberView(int $workspaceId, User $member, Request $request)
    {
        $workspace = Workspace::find($workspaceId);
        
        // Get member's guests with pivot data
        $guests = $workspace->users()
            ->wherePivot('role', 'guest')
            ->wherePivot('created_by_user_id', $member->id)
            ->withPivot(['attendance_days', 'absence_count', 'is_suspended'])
            ->get();

        // Get selected date (default to today)
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        $date = Carbon::parse($selectedDate);
        $dayName = strtolower($date->format('l'));

        Log::info('Member Attendance View Debug:', [
            'member_id' => $member->id,
            'selected_date' => $selectedDate,
            'day_name' => $dayName,
            'total_guests' => $guests->count(),
        ]);

        // Get attendance for all guests on selected date
        $attendanceData = [];
        foreach ($guests as $guest) {
            // Get guest's attendance days
            $attendanceDays = $guest->pivot->attendance_days ?? null;
            
            Log::info('Guest Attendance Days Check:', [
                'guest_id' => $guest->id,
                'guest_name' => $guest->name,
                'attendance_days_raw' => $attendanceDays,
                'is_string' => is_string($attendanceDays),
            ]);
            
            $attendanceDays = is_string($attendanceDays) ? json_decode($attendanceDays, true) : $attendanceDays;
            
            Log::info('After Decode:', [
                'attendance_days' => $attendanceDays,
                'day_name' => $dayName,
                'should_attend' => in_array($dayName, $attendanceDays ?? []),
            ]);
            
            $shouldAttend = in_array($dayName, $attendanceDays ?? []);
            
            // Only include guests who should attend on selected date
            if (!$shouldAttend) {
                continue;
            }

            $attendance = Attendance::forWorkspace($workspaceId)
                ->forGuest($guest->id)
                ->forDate($date)
                ->first();

            $attendanceData[] = [
                'guest' => $guest,
                'attendance' => $attendance,
                'should_attend' => $shouldAttend,
                'attendance_days' => $attendanceDays,
            ];
        }

        Log::info('Final Attendance Data:', [
            'filtered_guests_count' => count($attendanceData),
        ]);

        return view('attendance.member', compact('attendanceData', 'selectedDate', 'date'));
    }

    /**
     * Admin view - see all guests and suspended ones
     */
    private function adminView(int $workspaceId, Request $request)
    {
        $workspace = Workspace::find($workspaceId);
        
        // Get selected date (default to today)
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        $date = Carbon::parse($selectedDate);

        // Get all guests in workspace
        $guests = $workspace->users()
            ->wherePivot('role', 'guest')
            ->withPivot(['absence_count', 'is_suspended', 'attendance_days'])
            ->get();

        // Get attendance for all guests on selected date
        $attendanceData = [];
        $suspendedGuests = [];
        $absentToday = [];

        foreach ($guests as $guest) {
            $attendance = Attendance::forWorkspace($workspaceId)
                ->forGuest($guest->id)
                ->forDate($date)
                ->first();

            // Get guest's attendance days
            $attendanceDays = $guest->pivot->attendance_days ?? [];
            $attendanceDays = is_string($attendanceDays) ? json_decode($attendanceDays, true) : $attendanceDays;
            
            $dayName = strtolower($date->format('l'));
            $shouldAttend = in_array($dayName, $attendanceDays ?? []);

            $data = [
                'guest' => $guest,
                'attendance' => $attendance,
                'should_attend' => $shouldAttend,
                'absence_count' => $guest->pivot->absence_count ?? 0,
                'is_suspended' => $guest->pivot->is_suspended ?? false,
            ];

            $attendanceData[] = $data;

            // Track suspended guests
            if ($guest->pivot->is_suspended) {
                $suspendedGuests[] = $data;
            }

            // Track absences for today
            if ($shouldAttend && (!$attendance || $attendance->status === 'absent')) {
                $absentToday[] = $data;
            }
        }

        return view('attendance.admin', compact(
            'attendanceData', 
            'selectedDate', 
            'date', 
            'suspendedGuests',
            'absentToday'
        ));
    }

    /**
     * Guest check-in
     */
    public function checkIn(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if (!$user->isGuestInWorkspace($workspaceId)) {
            return back()->with('error', 'Only guests can check in.');
        }

        $today = now()->format('Y-m-d');
        
        // Check if already checked in today
        $attendance = Attendance::forWorkspace($workspaceId)
            ->forGuest($user->id)
            ->forDate($today)
            ->first();

        if ($attendance && $attendance->hasCheckedIn()) {
            return back()->with('error', 'You have already checked in today.');
        }

        // Create or update attendance
        $attendance = Attendance::updateOrCreate(
            [
                'workspace_id' => $workspaceId,
                'guest_id' => $user->id,
                'date' => $today,
            ],
            [
                'checked_in_at' => now()->format('H:i:s'),
                'status' => 'present',
            ]
        );

        return back()->with('success', 'Checked in successfully at ' . now()->format('h:i A'));
    }

    /**
     * Guest check-out
     */
    public function checkOut(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if (!$user->isGuestInWorkspace($workspaceId)) {
            return back()->with('error', 'Only guests can check out.');
        }

        $today = now()->format('Y-m-d');
        
        $attendance = Attendance::forWorkspace($workspaceId)
            ->forGuest($user->id)
            ->forDate($today)
            ->first();

        if (!$attendance || !$attendance->hasCheckedIn()) {
            return back()->with('error', 'You need to check in first.');
        }

        if ($attendance->hasCheckedOut()) {
            return back()->with('error', 'You have already checked out today.');
        }

        $attendance->update([
            'checked_out_at' => now()->format('H:i:s'),
        ]);

        return back()->with('success', 'Checked out successfully at ' . now()->format('h:i A'));
    }

    /**
     * Toggle attendance status (Admin/Member action)
     */
    public function toggleAttendance(Request $request, User $guest)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check permissions
        if (!$user->isAdminInWorkspace($workspaceId) && !$user->isMemberOnlyInWorkspace($workspaceId)) {
            abort(403);
        }

        $dateInput = $request->get('date', now()->format('Y-m-d'));
        $date = Carbon::parse($dateInput)->format('Y-m-d');
        $status = $request->get('status', 'present'); // 'present' or 'absent'

        // Find existing attendance
        $attendance = Attendance::where('workspace_id', $workspaceId)
            ->where('guest_id', $guest->id)
            ->whereDate('date', $date)
            ->first();
        
        $wasAbsent = $attendance && $attendance->status === 'absent';
        $isNewlyAbsent = $status === 'absent' && !$wasAbsent;

        if ($attendance) {
            // Update existing
            $attendance->status = $status;
            $attendance->notes = $request->get('notes');
            $attendance->save();
        } else {
            // Create new
            Attendance::create([
                'workspace_id' => $workspaceId,
                'guest_id' => $guest->id,
                'date' => $date,
                'status' => $status,
                'notes' => $request->get('notes'),
            ]);
        }

        // Handle absence count - only increment for NEW absences
        if ($isNewlyAbsent) {
            $workspace = Workspace::find($workspaceId);
            $guestPivot = $workspace->users()->where('user_id', $guest->id)->first();
            
            if ($guestPivot) {
                $absenceCount = ($guestPivot->pivot->absence_count ?? 0) + 1;
                
                // Suspend if 3 or more absences
                $isSuspended = $absenceCount >= 3;
                
                $workspace->users()->updateExistingPivot($guest->id, [
                    'absence_count' => $absenceCount,
                    'is_suspended' => $isSuspended,
                ]);

                if ($isSuspended) {
                    return back()->with('warning', 'Guest marked as absent. They have been suspended after 3 absences.');
                }
            }
        }

        $message = $status === 'present' ? 'Guest marked as present.' : 'Guest marked as absent.';
        return back()->with('success', $message);
    }

    /**
     * Mark guest as absent (Admin/Member action) - Legacy method
     */
    public function markAbsent(Request $request, User $guest)
    {
        $request->merge(['status' => 'absent']);
        return $this->toggleAttendance($request, $guest);
    }

    /**
     * Unsuspend a guest (Admin only)
     */
    public function unsuspend(User $guest)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if (!$user->isAdminInWorkspace($workspaceId) && !$user->isOwnerInWorkspace($workspaceId)) {
            abort(403);
        }

        $workspace = Workspace::find($workspaceId);
        $workspace->users()->updateExistingPivot($guest->id, [
            'is_suspended' => false,
            'absence_count' => 0, // Reset absence count
        ]);

        return back()->with('success', 'Guest unsuspended successfully.');
    }
}
